<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\MemberModel;
use App\Models\UserModel;
use App\Models\ChurchModel;
use App\Models\LifegroupModel;

class MemberController extends Controller
{
    private MemberModel $memberModel;
    private UserModel $userModel;
    private ChurchModel $churchModel;
    private LifegroupModel $lifegroupModel;
    
    public function __construct()
    {
        $this->memberModel = new MemberModel();
        $this->userModel = new UserModel();
        $this->churchModel = new ChurchModel();
        $this->lifegroupModel = new LifegroupModel();
    }
    
    public function index(): void
    {
        $this->requireAuth(); // Allow any authenticated user to view members
        
        $churchId = $_SESSION['church_id'] ?? null;
        $userRole = $this->getUserRole();
        
        // Get filter parameters
        $status = $_GET['status'] ?? '';
        $churchFilter = $_GET['church_id'] ?? '';
        $search = $_GET['search'] ?? '';
        
        $members = [];
        
        // If filters are applied, use search method (now includes hierarchy)
        if (!empty($status) || !empty($churchFilter) || !empty($search)) {
            $members = $this->memberModel->searchMembers($search, $churchFilter ? (int)$churchFilter : null);
            if (!empty($status)) {
                $members = array_filter($members, function($member) use ($status) {
                    return $member['status'] === $status;
                });
            }
        } else {
            // Get members based on user role
            switch ($userRole) {
                case ROLE_SUPER_ADMIN:
                    // Super admin can see all members with hierarchy
                    $members = $this->memberModel->getAllMembersWithHierarchy();
                    break;
                case ROLE_PASTOR:
                    $members = $this->memberModel->getMembersByPastor($_SESSION['user_id']);
                    break;
                case ROLE_COACH:
                    $members = $this->memberModel->getMembersByCoach($_SESSION['user_id']);
                    break;
                case ROLE_MENTOR:
                    $members = $this->memberModel->getMembersByMentor($_SESSION['user_id']);
                    break;
                default:
                    $members = [];
            }
        }
        
        // Always enhance member data with hierarchy information
        $enhancedMembers = [];
        foreach ($members as $member) {
            // Always get full hierarchy data for each member
            $enhancedMember = $this->memberModel->getMemberWithHierarchy($member['id']);
            if ($enhancedMember) {
                $enhancedMembers[] = $enhancedMember;
            } else {
                // Fallback to original member data if enhancement fails
                // Add default values for hierarchy fields
                $member['church_name'] = $member['church_name'] ?? 'Not Assigned';
                $member['pastor_name'] = $member['pastor_name'] ?? 'Not Assigned';
                $member['coach_name'] = $member['coach_name'] ?? 'Not Assigned';
                $member['mentor_name'] = $member['mentor_name'] ?? 'Not Assigned';
                $enhancedMembers[] = $member;
            }
        }
        
        $this->view('member/index', [
            'members' => $enhancedMembers,
            'churches' => $this->churchModel->getAllChurches(),
            'stats' => $this->memberModel->getMemberStats($churchId)
        ]);
    }
    
    public function create(): void
    {
        $this->requireRole([ROLE_MENTOR, ROLE_COACH]);
        
        $userRole = $this->getUserRole();
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        // Get churches based on user role
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can create members for any church
            $churches = $this->churchModel->getAllChurches();
        } else {
            // Pastors, coaches, and mentors can only create members for their own church
            if ($churchId) {
                $churches = [$this->churchModel->findById($churchId)];
            } else {
                $churches = [];
            }
        }
        
        // Get coaches based on user role and church
        $coaches = [];
        if ($userRole === ROLE_SUPER_ADMIN) {
            $coaches = $this->userModel->getCoachesForSelection();
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only assign members to themselves
            $currentCoach = $this->userModel->findById($_SESSION['user_id']);
            $coaches = [$currentCoach];
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can only assign members to their coach
            $currentMentor = $this->userModel->findById($_SESSION['user_id']);
            $currentCoach = $this->userModel->getHierarchyParent($_SESSION['user_id']);
            if ($currentCoach && $currentCoach['role'] === 'coach') {
                $coaches = [$currentCoach];
            } else {
                $coaches = [];
            }
        } elseif ($churchId) {
            $coaches = $this->userModel->getCoachesByChurch($churchId);
        }
        
        // Get mentors based on user role
        $mentors = [];
        if ($userRole === ROLE_SUPER_ADMIN) {
            $mentors = $this->userModel->getMentorsForSelection();
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can see mentors under them
            $mentors = $this->userModel->getMentorsByCoach($_SESSION['user_id']);
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can only assign members to themselves
            $currentMentor = $this->userModel->findById($_SESSION['user_id']);
            $mentors = [$currentMentor];
        } elseif ($churchId) {
            $mentors = $this->userModel->getMentorsByChurch($churchId);
        }

        // Get lifegroups based on user role
        $lifegroups = [];
        if ($userRole === ROLE_SUPER_ADMIN) {
            $lifegroups = $this->lifegroupModel->getAllLifegroups();
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can see lifegroups under their mentors
            $lifegroups = $this->lifegroupModel->getLifegroupsByCoach($_SESSION['user_id']);
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can only assign members to their own lifegroups
            $lifegroups = $this->lifegroupModel->getLifegroupsByMentor($_SESSION['user_id']);
        } elseif ($churchId) {
            $lifegroups = $this->lifegroupModel->getLifegroupsByChurch($churchId);
        }

        $this->view('member/create', [
            'churches' => $churches,
            'coaches' => $coaches,
            'mentors' => $mentors,
            'lifegroups' => $lifegroups,
            'userRole' => $userRole,
            'currentUserId' => $_SESSION['user_id'],
            'data' => [
                'church_id' => in_array($userRole, [ROLE_COACH, ROLE_MENTOR]) ? $churchId : null,
                'coach_id' => $userRole === ROLE_COACH ? $_SESSION['user_id'] : ($userRole === ROLE_MENTOR ? ($coaches[0]['id'] ?? null) : null),
                'mentor_id' => $userRole === ROLE_MENTOR ? $_SESSION['user_id'] : null,
                'lifegroup_id' => null
            ]
        ]);
    }
    
    public function store(): void
    {
        $this->requireRole([ROLE_MENTOR, ROLE_COACH]);
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'church_id' => $_POST['church_id'] ?? null,
            'status' => $_POST['status'] ?? 'active',
            'role' => 'member'
        ];
        
        // Validate required fields
        if (empty($data['name'])) {
            flash('Name is required', 'error');
            
            // Get restricted data for error view
            $userRole = $this->getUserRole();
            $churchId = $_SESSION['church_id'] ?? null;
            
            if ($churchId === null) {
                $user = $this->userModel->findById($_SESSION['user_id']);
                if ($user && isset($user['church_id'])) {
                    $churchId = $user['church_id'];
                }
            }
            
            $churches = [];
            $coaches = [];
            
            if ($userRole === ROLE_SUPER_ADMIN) {
                $churches = $this->churchModel->getAllChurches();
                $coaches = $this->userModel->getCoachesForSelection();
            } elseif ($churchId) {
                $churches = [$this->churchModel->findById($churchId)];
                $coaches = $this->userModel->getCoachesByChurch($churchId);
            }
            
            // Get mentors for error view
            $mentors = [];
            if ($userRole === ROLE_SUPER_ADMIN) {
                $mentors = $this->userModel->getMentorsForSelection();
            } elseif ($userRole === ROLE_COACH) {
                $mentors = $this->userModel->getMentorsByCoach($_SESSION['user_id']);
            } elseif ($userRole === ROLE_MENTOR) {
                $currentMentor = $this->userModel->findById($_SESSION['user_id']);
                $mentors = [$currentMentor];
            } elseif ($churchId) {
                $mentors = $this->userModel->getMentorsByChurch($churchId);
            }

            $this->view('member/create', [
                'data' => array_merge($data, [
                    'coach_id' => $_POST['coach_id'] ?? null,
                    'mentor_id' => $_POST['mentor_id'] ?? null,
                    'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                ]),
                'churches' => $churches,
                'coaches' => $coaches,
                'mentors' => $mentors,
                'lifegroups' => $userRole === ROLE_MENTOR ? $this->lifegroupModel->getLifegroupsByMentor($_SESSION['user_id']) : [],
                'userRole' => $userRole,
                'currentUserId' => $_SESSION['user_id']
            ]);
            return;
        }
        
        // Validate email if provided
        if (!empty($data['email'])) {
            // Check if email already exists
            if ($this->userModel->findByEmail($data['email'])) {
                flash('Email address already exists', 'error');
                
                // Get restricted data for error view
                $userRole = $this->getUserRole();
                $churchId = $_SESSION['church_id'] ?? null;
                
                if ($churchId === null) {
                    $user = $this->userModel->findById($_SESSION['user_id']);
                    if ($user && isset($user['church_id'])) {
                        $churchId = $user['church_id'];
                    }
                }
                
                $churches = [];
                $coaches = [];
                
                if ($userRole === ROLE_SUPER_ADMIN) {
                    $churches = $this->churchModel->getAllChurches();
                    $coaches = $this->userModel->getCoachesForSelection();
                } elseif ($userRole === ROLE_COACH) {
                    // Coaches can only assign members to themselves
                    $churches = [$this->churchModel->findById($churchId)];
                    $coaches = [$this->userModel->findById($_SESSION['user_id'])];
                } elseif ($userRole === ROLE_MENTOR) {
                    // Mentors can only assign members to their church and coach
                    $churches = [$this->churchModel->findById($churchId)];
                    $currentCoach = $this->userModel->getHierarchyParent($_SESSION['user_id']);
                    if ($currentCoach && $currentCoach['role'] === 'coach') {
                        $coaches = [$currentCoach];
                    } else {
                        $coaches = [];
                    }
                } elseif ($churchId) {
                    $churches = [$this->churchModel->findById($churchId)];
                    $coaches = $this->userModel->getCoachesByChurch($churchId);
                }
                
                // Get mentors for error view
                $mentors = [];
                if ($userRole === ROLE_SUPER_ADMIN) {
                    $mentors = $this->userModel->getMentorsForSelection();
                } elseif ($userRole === ROLE_COACH) {
                    $mentors = $this->userModel->getMentorsByCoach($_SESSION['user_id']);
                } elseif ($userRole === ROLE_MENTOR) {
                    $currentMentor = $this->userModel->findById($_SESSION['user_id']);
                    $mentors = [$currentMentor];
                } elseif ($churchId) {
                    $mentors = $this->userModel->getMentorsByChurch($churchId);
                }
                
                $this->view('member/create', [
                    'data' => array_merge($data, [
                        'coach_id' => $_POST['coach_id'] ?? null,
                        'mentor_id' => $_POST['mentor_id'] ?? null,
                        'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                    ]),
                    'churches' => $churches,
                    'coaches' => $coaches,
                    'mentors' => $mentors,
                    'lifegroups' => $userRole === ROLE_MENTOR ? $this->lifegroupModel->getLifegroupsByMentor($_SESSION['user_id']) : [],
                    'userRole' => $userRole,
                    'currentUserId' => $_SESSION['user_id']
                ]);
                return;
            }
        }
        
        // Validate password if provided
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                flash('Password must be at least 6 characters long', 'error');
                
                // Get restricted data for error view
                $userRole = $this->getUserRole();
                $churchId = $_SESSION['church_id'] ?? null;
                
                if ($churchId === null) {
                    $user = $this->userModel->findById($_SESSION['user_id']);
                    if ($user && isset($user['church_id'])) {
                        $churchId = $user['church_id'];
                    }
                }
                
                $churches = [];
                $coaches = [];
                
                if ($userRole === ROLE_SUPER_ADMIN) {
                    $churches = $this->churchModel->getAllChurches();
                    $coaches = $this->userModel->getCoachesForSelection();
                } elseif ($userRole === ROLE_COACH) {
                    // Coaches can only assign members to themselves
                    $churches = [$this->churchModel->findById($churchId)];
                    $coaches = [$this->userModel->findById($_SESSION['user_id'])];
                } elseif ($userRole === ROLE_MENTOR) {
                    // Mentors can only assign members to their church and coach
                    $churches = [$this->churchModel->findById($churchId)];
                    $currentCoach = $this->userModel->getHierarchyParent($_SESSION['user_id']);
                    if ($currentCoach && $currentCoach['role'] === 'coach') {
                        $coaches = [$currentCoach];
                    } else {
                        $coaches = [];
                    }
                } elseif ($churchId) {
                    $churches = [$this->churchModel->findById($churchId)];
                    $coaches = $this->userModel->getCoachesByChurch($churchId);
                }
                
                // Get mentors for error view
                $mentors = [];
                if ($userRole === ROLE_SUPER_ADMIN) {
                    $mentors = $this->userModel->getMentorsForSelection();
                } elseif ($userRole === ROLE_COACH) {
                    $mentors = $this->userModel->getMentorsByCoach($_SESSION['user_id']);
                } elseif ($userRole === ROLE_MENTOR) {
                    $currentMentor = $this->userModel->findById($_SESSION['user_id']);
                    $mentors = [$currentMentor];
                } elseif ($churchId) {
                    $mentors = $this->userModel->getMentorsByChurch($churchId);
                }
                
                $this->view('member/create', [
                    'data' => array_merge($data, [
                        'coach_id' => $_POST['coach_id'] ?? null,
                        'mentor_id' => $_POST['mentor_id'] ?? null,
                        'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                    ]),
                    'churches' => $churches,
                    'coaches' => $coaches,
                    'mentors' => $mentors,
                    'lifegroups' => $userRole === ROLE_MENTOR ? $this->lifegroupModel->getLifegroupsByMentor($_SESSION['user_id']) : [],
                    'userRole' => $userRole,
                    'currentUserId' => $_SESSION['user_id']
                ]);
                return;
            }
        }
        
        $userId = $this->userModel->create($data);
        
        if ($userId) {
            $userRole = $this->getUserRole();
            
            // Handle coach assignment for coaches and mentors
            if ($userRole === ROLE_COACH && !empty($_POST['coach_id'])) {
                $coachId = (int)$_POST['coach_id'];
                // Ensure the coach is the current user
                if ($coachId === (int)$_SESSION['user_id']) {
                    // Create hierarchy relationship for the member to the coach
                    $this->userModel->createHierarchyRelationship($userId, $coachId);
                }
            } elseif ($userRole === ROLE_MENTOR && !empty($_POST['coach_id'])) {
                $coachId = (int)$_POST['coach_id'];
                // Ensure the coach is the mentor's parent
                $currentCoach = $this->userModel->getHierarchyParent($_SESSION['user_id']);
                if ($currentCoach && $currentCoach['role'] === 'coach' && $coachId === $currentCoach['id']) {
                    // Create hierarchy relationship for the member to the coach
                    $this->userModel->createHierarchyRelationship($userId, $coachId);
                }
            }
            
            // Handle mentor assignment (members are assigned to mentors)
            if (!empty($_POST['mentor_id'])) {
                $mentorId = (int)$_POST['mentor_id'];
                $this->userModel->createHierarchyRelationship($userId, $mentorId);
                
                // If coach is also selected, ensure the mentor is assigned to the coach
                if (!empty($_POST['coach_id'])) {
                    $coachId = (int)$_POST['coach_id'];
                    $coachMentor = $this->userModel->getHierarchyParent($mentorId);
                    if (!$coachMentor || $coachMentor['id'] != $coachId) {
                        $this->userModel->updateHierarchyRelationship($mentorId, $coachId);
                    }
                }
            }
            
            // Handle lifegroup assignment
            if (!empty($_POST['lifegroup_id'])) {
                $lifegroupId = (int)$_POST['lifegroup_id'];
                $this->lifegroupModel->addMemberToLifegroup($lifegroupId, $userId);
            }
            
            flash('Member created successfully', 'success');
            $this->redirect('/member');
        } else {
            flash('Failed to create member', 'error');
            
            // Get restricted data for error view
            $userRole = $this->getUserRole();
            $churchId = $_SESSION['church_id'] ?? null;
            
            if ($churchId === null) {
                $user = $this->userModel->findById($_SESSION['user_id']);
                if ($user && isset($user['church_id'])) {
                    $churchId = $user['church_id'];
                }
            }
            
            $churches = [];
            $coaches = [];
            
            if ($userRole === ROLE_SUPER_ADMIN) {
                $churches = $this->churchModel->getAllChurches();
                $coaches = $this->userModel->getCoachesForSelection();
            } elseif ($userRole === ROLE_COACH) {
                // Coaches can only assign members to themselves
                $churches = [$this->churchModel->findById($churchId)];
                $coaches = [$this->userModel->findById($_SESSION['user_id'])];
            } elseif ($userRole === ROLE_MENTOR) {
                // Mentors can only assign members to their church and coach
                $churches = [$this->churchModel->findById($churchId)];
                $currentCoach = $this->userModel->getHierarchyParent($_SESSION['user_id']);
                if ($currentCoach && $currentCoach['role'] === 'coach') {
                    $coaches = [$currentCoach];
                } else {
                    $coaches = [];
                }
            } elseif ($churchId) {
                $churches = [$this->churchModel->findById($churchId)];
                $coaches = $this->userModel->getCoachesByChurch($churchId);
            }
            
            // Get mentors for error view
            $mentors = [];
            if ($userRole === ROLE_SUPER_ADMIN) {
                $mentors = $this->userModel->getMentorsForSelection();
            } elseif ($userRole === ROLE_COACH) {
                $mentors = $this->userModel->getMentorsByCoach($_SESSION['user_id']);
            } elseif ($userRole === ROLE_MENTOR) {
                $currentMentor = $this->userModel->findById($_SESSION['user_id']);
                $mentors = [$currentMentor];
            } elseif ($churchId) {
                $mentors = $this->userModel->getMentorsByChurch($churchId);
            }

            $this->view('member/create', [
                'data' => array_merge($data, [
                    'coach_id' => $_POST['coach_id'] ?? null,
                    'mentor_id' => $_POST['mentor_id'] ?? null,
                    'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                ]),
                'churches' => $churches,
                'coaches' => $coaches,
                'mentors' => $mentors,
                'lifegroups' => $userRole === ROLE_MENTOR ? $this->lifegroupModel->getLifegroupsByMentor($_SESSION['user_id']) : [],
                'userRole' => $userRole,
                'currentUserId' => $_SESSION['user_id']
            ]);
        }
    }
    
    public function edit(string $id): void
    {
        $this->requireRole([ROLE_MENTOR, ROLE_COACH]);
        
        $member = $this->userModel->findById((int) $id);
        
        if (!$member || $member['role'] !== 'member') {
            flash('Member not found', 'error');
            $this->redirect('/member');
        }
        
        $userRole = $this->getUserRole();
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        // Get churches based on user role
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can edit members from any church
            $churches = $this->churchModel->getAllChurches();
        } else {
            // Pastors, coaches, and mentors can only edit members from their own church
            if ($churchId) {
                $churches = [$this->churchModel->findById($churchId)];
            } else {
                $churches = [];
            }
        }
        
        // Get coaches based on user role and church
        $coaches = [];
        if ($userRole === ROLE_SUPER_ADMIN) {
            $coaches = $this->userModel->getCoachesForSelection();
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only assign members to themselves
            $coaches = [$this->userModel->findById($_SESSION['user_id'])];
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can only assign members to their coach
            $currentCoach = $this->userModel->getHierarchyParent($_SESSION['user_id']);
            if ($currentCoach && $currentCoach['role'] === 'coach') {
                $coaches = [$currentCoach];
            } else {
                $coaches = [];
            }
        } elseif ($churchId) {
            $coaches = $this->userModel->getCoachesByChurch($churchId);
        }
        
        // Get current mentor assignment (members are directly assigned to mentors)
        $currentMentor = $this->userModel->getHierarchyParent((int) $id);
        $currentCoach = null;
        
        // If there's a mentor, get the coach through the mentor
        if ($currentMentor && $currentMentor['role'] === 'mentor') {
            $currentCoach = $this->userModel->getHierarchyParent($currentMentor['id']);
        }
        
        // Get mentors based on user role
        $mentors = [];
        if ($userRole === ROLE_SUPER_ADMIN) {
            $mentors = $this->userModel->getMentorsForSelection();
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can see mentors under them
            $mentors = $this->userModel->getMentorsByCoach($_SESSION['user_id']);
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can only assign members to themselves
            $currentMentor = $this->userModel->findById($_SESSION['user_id']);
            $mentors = [$currentMentor];
        } elseif ($churchId) {
            $mentors = $this->userModel->getMentorsByChurch($churchId);
        }

        // Get lifegroups based on user role
        $lifegroups = [];
        if ($userRole === ROLE_SUPER_ADMIN) {
            $lifegroups = $this->lifegroupModel->getAllLifegroups();
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can see lifegroups under their mentors
            $lifegroups = $this->lifegroupModel->getLifegroupsByCoach($_SESSION['user_id']);
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can only assign members to their own lifegroups
            $lifegroups = $this->lifegroupModel->getLifegroupsByMentor($_SESSION['user_id']);
        } elseif ($churchId) {
            $lifegroups = $this->lifegroupModel->getLifegroupsByChurch($churchId);
        }

        // Get current lifegroup assignment
        $currentLifegroup = $this->lifegroupModel->getMemberLifegroup((int) $id);
        
        $this->view('member/edit', [
            'member' => $member, 
            'churches' => $churches,
            'coaches' => $coaches,
            'mentors' => $mentors,
            'lifegroups' => $lifegroups,
            'currentCoach' => $currentCoach,
            'currentMentor' => $currentMentor,
            'currentLifegroup' => $currentLifegroup,
            'userRole' => $userRole,
            'currentUserId' => $_SESSION['user_id']
        ]);
    }
    
    public function update(string $id): void
    {
        $this->requireRole([ROLE_MENTOR, ROLE_COACH]);
        
        $memberId = (int) $id;
        $member = $this->userModel->findById($memberId);
        
        if (!$member || $member['role'] !== 'member') {
            flash('Member not found', 'error');
            $this->redirect('/member');
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'church_id' => $_POST['church_id'] ?? null,
            'status' => $_POST['status'] ?? 'active'
        ];
        
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        
        // Validate required fields
        if (empty($data['name'])) {
            flash('Name is required', 'error');
            
            // Get restricted data for error view
            $userRole = $this->getUserRole();
            $churchId = $_SESSION['church_id'] ?? null;
            
            if ($churchId === null) {
                $user = $this->userModel->findById($_SESSION['user_id']);
                if ($user && isset($user['church_id'])) {
                    $churchId = $user['church_id'];
                }
            }
            
            $churches = [];
            $coaches = [];
            
            if ($userRole === ROLE_SUPER_ADMIN) {
                $churches = $this->churchModel->getAllChurches();
                $coaches = $this->userModel->getCoachesForSelection();
            } elseif ($userRole === ROLE_COACH) {
                // Coaches can only assign members to themselves
                $churches = [$this->churchModel->findById($churchId)];
                $coaches = [$this->userModel->findById($_SESSION['user_id'])];
            } elseif ($userRole === ROLE_MENTOR) {
                // Mentors can only assign members to their church and coach
                $churches = [$this->churchModel->findById($churchId)];
                $currentCoach = $this->userModel->getHierarchyParent($_SESSION['user_id']);
                if ($currentCoach && $currentCoach['role'] === 'coach') {
                    $coaches = [$currentCoach];
                } else {
                    $coaches = [];
                }
            } elseif ($churchId) {
                $churches = [$this->churchModel->findById($churchId)];
                $coaches = $this->userModel->getCoachesByChurch($churchId);
            }
            
            // Get mentors for error view
            $mentors = [];
            if ($userRole === ROLE_SUPER_ADMIN) {
                $mentors = $this->userModel->getMentorsForSelection();
            } elseif ($userRole === ROLE_COACH) {
                $mentors = $this->userModel->getMentorsByCoach($_SESSION['user_id']);
            } elseif ($userRole === ROLE_MENTOR) {
                $currentMentor = $this->userModel->findById($_SESSION['user_id']);
                $mentors = [$currentMentor];
            } elseif ($churchId) {
                $mentors = $this->userModel->getMentorsByChurch($churchId);
            }
            
            $this->view('member/edit', [
                'member' => array_merge($member, $data, [
                    'coach_id' => $_POST['coach_id'] ?? null,
                    'mentor_id' => $_POST['mentor_id'] ?? null,
                    'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                ]),
                'churches' => $churches,
                'coaches' => $coaches,
                'mentors' => $mentors,
                'currentCoach' => null,
                'currentMentor' => null,
                'currentLifegroup' => null
            ]);
            return;
        }
        
        // Validate email if provided
        if (!empty($data['email'])) {
            // Check if email already exists (excluding current user)
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser && $existingUser['id'] != $memberId) {
                flash('Email address already exists', 'error');
                
                // Get restricted data for error view
                $userRole = $this->getUserRole();
                $churchId = $_SESSION['church_id'] ?? null;
                
                if ($churchId === null) {
                    $user = $this->userModel->findById($_SESSION['user_id']);
                    if ($user && isset($user['church_id'])) {
                        $churchId = $user['church_id'];
                    }
                }
                
                $churches = [];
                $coaches = [];
                
                if ($userRole === ROLE_SUPER_ADMIN) {
                    $churches = $this->churchModel->getAllChurches();
                    $coaches = $this->userModel->getCoachesForSelection();
                } elseif ($churchId) {
                    $churches = [$this->churchModel->findById($churchId)];
                    $coaches = $this->userModel->getCoachesByChurch($churchId);
                }
                
                $this->view('member/edit', [
                    'member' => array_merge($member, $data, [
                        'coach_id' => $_POST['coach_id'] ?? null,
                        'mentor_id' => $_POST['mentor_id'] ?? null,
                        'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                    ]),
                    'churches' => $churches,
                    'coaches' => $coaches,
                    'currentCoach' => null,
                    'currentMentor' => null,
                    'currentLifegroup' => null
                ]);
                return;
            }
        }
        
        if ($this->userModel->update($memberId, $data)) {
            // Handle mentor assignment (members are assigned to mentors)
            $mentorId = !empty($_POST['mentor_id']) ? (int)$_POST['mentor_id'] : null;
            $this->userModel->updateHierarchyRelationship($memberId, $mentorId);
            
            // If mentor is selected and coach is also selected, ensure the mentor is assigned to the coach
            if (!empty($_POST['mentor_id']) && !empty($_POST['coach_id'])) {
                $coachId = (int)$_POST['coach_id'];
                $mentorId = (int)$_POST['mentor_id'];
                
                // Check if mentor is already assigned to this coach
                $mentorCoach = $this->userModel->getHierarchyParent($mentorId);
                if (!$mentorCoach || $mentorCoach['id'] != $coachId) {
                    $this->userModel->updateHierarchyRelationship($mentorId, $coachId);
                }
            }
            
            // Handle lifegroup assignment
            if (!empty($_POST['lifegroup_id'])) {
                $lifegroupId = (int)$_POST['lifegroup_id'];
                // First remove from any existing lifegroup
                $this->lifegroupModel->removeMemberFromAllLifegroups($memberId);
                // Then add to the new lifegroup
                $this->lifegroupModel->addMemberToLifegroup($lifegroupId, $memberId);
            } else {
                // If no lifegroup is selected, remove from any existing lifegroup
                $this->lifegroupModel->removeMemberFromAllLifegroups($memberId);
            }
            
            flash('Member updated successfully', 'success');
            $this->redirect('/member');
        } else {
            flash('Failed to update member', 'error');
            
            // Get restricted data for error view
            $userRole = $this->getUserRole();
            $churchId = $_SESSION['church_id'] ?? null;
            
            if ($churchId === null) {
                $user = $this->userModel->findById($_SESSION['user_id']);
                if ($user && isset($user['church_id'])) {
                    $churchId = $user['church_id'];
                }
            }
            
            $churches = [];
            $coaches = [];
            
            if ($userRole === ROLE_SUPER_ADMIN) {
                $churches = $this->churchModel->getAllChurches();
                $coaches = $this->userModel->getCoachesForSelection();
            } elseif ($churchId) {
                $churches = [$this->churchModel->findById($churchId)];
                $coaches = $this->userModel->getCoachesByChurch($churchId);
            }
            
            $this->view('member/edit', [
                'member' => array_merge($member, $data, [
                    'coach_id' => $_POST['coach_id'] ?? null,
                    'mentor_id' => $_POST['mentor_id'] ?? null,
                    'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                ]),
                'churches' => $churches,
                'coaches' => $coaches,
                'currentCoach' => null,
                'currentMentor' => null,
                'currentLifegroup' => null
            ]);
        }
    }
    
    public function delete(string $id): void
    {
        $this->requireRole([ROLE_MENTOR, ROLE_COACH]);
        
        $memberId = (int) $id;
        $member = $this->userModel->findById($memberId);
        
        if (!$member || $member['role'] !== 'member') {
            flash('Member not found', 'error');
            $this->redirect('/member');
        }
        
        if ($this->userModel->delete($memberId)) {
            // Clear hierarchy relationships
            $this->userModel->updateHierarchyRelationship($memberId, null);
            
            flash('Member deleted successfully', 'success');
        } else {
            flash('Failed to delete member', 'error');
        }
        
        $this->redirect('/member');
    }
    
    public function updateStatus(string $id): void
    {
        $this->requireRole([ROLE_MENTOR, ROLE_COACH]);
        
        $memberId = (int) $id;
        $status = $_POST['status'] ?? '';
        
        if (!in_array($status, [STATUS_ACTIVE, STATUS_INACTIVE, STATUS_PENDING, STATUS_SUSPENDED])) {
            flash('Invalid status', 'error');
            $this->redirect('/member');
        }
        
        if ($this->memberModel->updateMemberStatus($memberId, $status)) {
            flash('Member status updated successfully', 'success');
        } else {
            flash('Failed to update member status', 'error');
        }
        
        $this->redirect('/member');
    }
    
    public function getCoachesByChurch(string $churchId): void
    {
        $this->requireRole([ROLE_MENTOR, ROLE_COACH]);
        
        $coaches = $this->userModel->getCoachesByChurch((int) $churchId);
        
        header('Content-Type: application/json');
        echo json_encode($coaches);
        exit;
    }
    
    public function getMentorsByChurch(string $churchId): void
    {
        $this->requireRole([ROLE_MENTOR, ROLE_COACH]);
        
        $mentors = $this->userModel->getMentorsByChurch((int) $churchId);
        
        header('Content-Type: application/json');
        echo json_encode($mentors);
        exit;
    }
    
    public function getMentorsByCoach(string $coachId): void
    {
        $this->requireRole([ROLE_MENTOR, ROLE_COACH]);
        
        $mentors = $this->userModel->getMentorsByCoach((int) $coachId);
        
        header('Content-Type: application/json');
        echo json_encode($mentors);
        exit;
    }
    
    public function getLifegroupsByMentor(string $mentorId): void
    {
        $this->requireRole([ROLE_MENTOR, ROLE_COACH]);
        
        $lifegroups = $this->lifegroupModel->getLifegroupsByMentor((int) $mentorId);
        
        header('Content-Type: application/json');
        echo json_encode($lifegroups);
        exit;
    }
} 