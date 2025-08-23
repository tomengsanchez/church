<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use App\Models\ChurchModel;
use App\Models\LifegroupModel;
use App\Models\MemberStatusModel;

class FriendController extends Controller
{
    private UserModel $userModel;
    private ChurchModel $churchModel;
    private LifegroupModel $lifegroupModel;
    private MemberStatusModel $memberStatusModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->churchModel = new ChurchModel();
        $this->lifegroupModel = new LifegroupModel();
        $this->memberStatusModel = new MemberStatusModel();
    }
    
    public function index(): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);
        
        $churchId = $_SESSION['church_id'] ?? null;
        $userRole = $this->getUserRole();
        
        // Get filter parameters
        $status = $_GET['status'] ?? '';
        $churchFilter = $_GET['church_id'] ?? '';
        $search = $_GET['search'] ?? '';
        
        $friends = [];
        
        // Get friends (members with pending status) based on user role
        switch ($userRole) {
            case ROLE_SUPER_ADMIN:
                // Super admin can see all pending friends
                $friends = $this->userModel->getFriendsByStatus('pending');
                break;
            case ROLE_PASTOR:
                $friends = $this->userModel->getFriendsByPastor($_SESSION['user_id']);
                break;
            case ROLE_COACH:
                $friends = $this->userModel->getFriendsByCoach($_SESSION['user_id']);
                break;
            case ROLE_MENTOR:
                $friends = $this->userModel->getFriendsByMentor($_SESSION['user_id']);
                break;
            default:
                $friends = [];
        }
        
        // Apply filters
        if (!empty($status)) {
            $friends = array_filter($friends, function($friend) use ($status) {
                return $friend['status'] === $status;
            });
        }
        
        if (!empty($churchFilter)) {
            $friends = array_filter($friends, function($friend) use ($churchFilter) {
                return $friend['church_id'] == $churchFilter;
            });
        }
        
        if (!empty($search)) {
            $friends = array_filter($friends, function($friend) use ($search) {
                return stripos($friend['name'], $search) !== false || 
                       stripos($friend['email'], $search) !== false;
            });
        }
        
        $this->view('friend/index', [
            'friends' => $friends,
            'churches' => $this->churchModel->getAllChurches(),
            'stats' => $this->getFriendStats($churchId)
        ]);
    }
    
    public function create(): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);
        
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
            // Super admin can create friends for any church
            $churches = $this->churchModel->getAllChurches();
        } else {
            // Pastors, coaches, and mentors can only create friends for their own church
            if ($churchId) {
                $churches = [$this->churchModel->findById($churchId)];
            } else {
                $churches = [];
            }
        }

        $this->view('friend/create', [
            'churches' => $churches,
            'userRole' => $userRole,
            'currentUserId' => $_SESSION['user_id'],
            'data' => [
                'church_id' => in_array($userRole, [ROLE_COACH, ROLE_MENTOR]) ? $churchId : null,
                'status' => 'pending'
            ]
        ]);
    }
    
    public function store(): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'church_id' => !empty($_POST['church_id']) ? $_POST['church_id'] : null,
            'status' => 'pending', // Always pending for new friends
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
            if ($userRole === ROLE_SUPER_ADMIN) {
                $churches = $this->churchModel->getAllChurches();
            } elseif ($churchId) {
                $churches = [$this->churchModel->findById($churchId)];
            }

            $this->view('friend/create', [
                'data' => $data,
                'churches' => $churches,
                'userRole' => $userRole,
                'currentUserId' => $_SESSION['user_id']
            ]);
            return;
        }
        
        // Validate email if provided
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                flash('Invalid email format', 'error');
                
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
                if ($userRole === ROLE_SUPER_ADMIN) {
                    $churches = $this->churchModel->getAllChurches();
                } elseif ($churchId) {
                    $churches = [$this->churchModel->findById($churchId)];
                }

                $this->view('friend/create', [
                    'data' => $data,
                    'churches' => $churches,
                    'userRole' => $userRole,
                    'currentUserId' => $_SESSION['user_id']
                ]);
                return;
            }
            
            // Check if email already exists
            if ($this->userModel->findByEmail($data['email'])) {
                flash('Email already exists', 'error');
                
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
                if ($userRole === ROLE_SUPER_ADMIN) {
                    $churches = $this->churchModel->getAllChurches();
                } elseif ($churchId) {
                    $churches = [$this->churchModel->findById($churchId)];
                }

                $this->view('friend/create', [
                    'data' => $data,
                    'churches' => $churches,
                    'userRole' => $userRole,
                    'currentUserId' => $_SESSION['user_id']
                ]);
                return;
            }
        }
        
        // Create the friend (member with pending status)
        try {
            $friendId = $this->userModel->create($data);
            
            logInfo('New friend added', [
                'friend_id' => $friendId,
                'name' => $data['name'],
                'added_by' => $_SESSION['user_id'],
                'church_id' => $data['church_id']
            ]);
            
            flash('New friend added successfully!', 'success');
            $this->redirect('/friend');
            
        } catch (\Exception $e) {
            logError('Failed to add new friend', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            flash('Failed to add new friend. Please try again.', 'error');
            
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
            if ($userRole === ROLE_SUPER_ADMIN) {
                $churches = $this->churchModel->getAllChurches();
            } elseif ($churchId) {
                $churches = [$this->churchModel->findById($churchId)];
            }

            $this->view('friend/create', [
                'data' => $data,
                'churches' => $churches,
                'userRole' => $userRole,
                'currentUserId' => $_SESSION['user_id']
            ]);
        }
    }
    
    public function edit(string $id): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);
        
        $friend = $this->userModel->findById((int) $id);
        
        if (!$friend || $friend['role'] !== 'member' || $friend['status'] !== 'pending') {
            flash('Friend not found or not in pending status', 'error');
            $this->redirect('/friend');
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
            // Super admin can edit friends for any church
            $churches = $this->churchModel->getAllChurches();
        } else {
            // Pastors, coaches, and mentors can only edit friends for their own church
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
            // Coaches can only assign friends to themselves
            $coaches = [$this->userModel->findById($_SESSION['user_id'])];
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can only assign friends to their coach
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
            // Mentors can only assign friends to themselves
            $mentors = [$this->userModel->findById($_SESSION['user_id'])];
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
            // Mentors can only assign friends to their own lifegroups
            $lifegroups = $this->lifegroupModel->getLifegroupsByMentor($_SESSION['user_id']);
        } elseif ($churchId) {
            $lifegroups = $this->lifegroupModel->getLifegroupsByChurch($churchId);
        }

        // Get current mentor assignment (friends are directly assigned to mentors)
        $currentMentor = $this->userModel->getHierarchyParent((int) $id);
        $currentCoach = null;
        
        // If there's a mentor, get the coach through the mentor
        if ($currentMentor && $currentMentor['role'] === 'mentor') {
            $currentCoach = $this->userModel->getHierarchyParent($currentMentor['id']);
        }
        
        // Get current lifegroup assignment
        $currentLifegroup = $this->lifegroupModel->getMemberLifegroup((int) $id);
        
        $this->view('friend/edit', [
            'friend' => $friend, 
            'churches' => $churches,
            'coaches' => $coaches,
            'mentors' => $mentors,
            'lifegroups' => $lifegroups,
            'currentCoach' => $currentCoach,
            'currentMentor' => $currentMentor,
            'currentLifegroup' => $currentLifegroup,
            'userRole' => $userRole,
            'currentUserId' => $_SESSION['user_id'],
            'memberStatuses' => $this->memberStatusModel->getAllActive()
        ]);
    }
    
    public function update(string $id): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);
        
        $friendId = (int) $id;
        $friend = $this->userModel->findById($friendId);
        
        if (!$friend || $friend['role'] !== 'member' || $friend['status'] !== 'pending') {
            flash('Friend not found or not in pending status', 'error');
            $this->redirect('/friend');
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'church_id' => !empty($_POST['church_id']) ? $_POST['church_id'] : null,
            'status' => $_POST['status'] ?? 'pending'
        ];

        // Validate status against active statuses
        $validStatuses = array_map(fn($s) => $s['slug'], $this->memberStatusModel->getAllActive());
        if (!in_array($data['status'], $validStatuses, true)) {
            flash('Invalid status', 'error');
            $this->redirect("/friend/edit/{$friendId}");
            return;
        }
        
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        
        // Validate required fields
        if (empty($data['name'])) {
            flash('Name is required', 'error');
            $this->redirect("/friend/edit/{$friendId}");
            return;
        }
        
        // Validate email if provided
        if (!empty($data['email'])) {
            // Check if email already exists (excluding current friend)
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser && $existingUser['id'] != $friendId) {
                flash('Email address already exists', 'error');
                $this->redirect("/friend/edit/{$friendId}");
                return;
            }
        }
        
        if ($this->userModel->update($friendId, $data)) {
            // Handle mentor assignment (friends are assigned to mentors)
            $mentorId = !empty($_POST['mentor_id']) ? (int)$_POST['mentor_id'] : null;
            $this->userModel->updateHierarchyRelationship($friendId, $mentorId);
            
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
            
            // IMPORTANT: Remove any direct friend-to-coach relationships
            // Friends should only be assigned to mentors, not directly to coaches
            $this->userModel->removeDirectCoachRelationships($friendId);
            
            // Handle lifegroup assignment
            if (!empty($_POST['lifegroup_id'])) {
                $lifegroupId = (int)$_POST['lifegroup_id'];
                // First remove from any existing lifegroup
                $this->lifegroupModel->removeMemberFromAllLifegroups($friendId);
                // Then add to the new lifegroup
                $this->lifegroupModel->addMemberToLifegroup($lifegroupId, $friendId);
            } else {
                // If no lifegroup is selected, remove from any existing lifegroup
                $this->lifegroupModel->removeMemberFromAllLifegroups($friendId);
            }
            
            flash('Friend updated successfully', 'success');
            $this->redirect('/friend');
        } else {
            flash('Failed to update friend', 'error');
            $this->redirect("/friend/edit/{$friendId}");
        }
    }
    
    public function delete(string $id): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);
        
        $friendId = (int) $id;
        $friend = $this->userModel->findById($friendId);
        
        if (!$friend || $friend['role'] !== 'member' || $friend['status'] !== 'pending') {
            flash('Friend not found or not in pending status', 'error');
            $this->redirect('/friend');
        }
        
        if ($this->userModel->delete($friendId)) {
            // Clear hierarchy relationships
            $this->userModel->updateHierarchyRelationship($friendId, null);
            
            flash('Friend deleted successfully', 'success');
        } else {
            flash('Failed to delete friend', 'error');
        }
        
        $this->redirect('/friend');
    }
    
    public function promote(string $id): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);
        
        $friendId = (int) $id;
        $friend = $this->userModel->findById($friendId);
        
        if (!$friend || $friend['role'] !== 'member' || $friend['status'] !== 'pending') {
            flash('Friend not found or not in pending status', 'error');
            $this->redirect('/friend');
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
        
        // Get available data for the promotion form
        $churches = [];
        $pastors = [];
        $coaches = [];
        $mentors = [];
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            $churches = $this->churchModel->getAllChurches();
            $pastors = $this->userModel->getUsersByRole('pastor');
            $coaches = $this->userModel->getUsersByRole('coach');
            $mentors = $this->userModel->getMentorsWithChurchesAndPastors();
        } elseif ($churchId) {
            $churches = [$this->churchModel->findById($churchId)];
            $pastors = $this->userModel->getUsersByRoleAndChurch('pastor', $churchId);
            $coaches = $this->userModel->getUsersByRoleAndChurch('coach', $churchId);
            $mentors = $this->userModel->getMentorsWithChurchesAndPastorsByChurch($churchId);
        }
        
        $this->view('friend/promote', [
            'friend' => $friend,
            'churches' => $churches,
            'pastors' => $pastors,
            'coaches' => $coaches,
            'mentors' => $mentors,
            'userRole' => $userRole,
            'currentUserId' => $_SESSION['user_id']
        ]);
    }
    
    public function processPromotion(string $id): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);
        
        $friendId = (int) $id;
        $friend = $this->userModel->findById($friendId);
        
        if (!$friend || $friend['role'] !== 'member' || $friend['status'] !== 'pending') {
            flash('Friend not found or not in pending status', 'error');
            $this->redirect('/friend');
        }
        
        // Validate required fields for promotion
        if (empty($_POST['church_id']) || empty($_POST['pastor_id']) || empty($_POST['coach_id'])) {
            flash('Church, Pastor, and Coach are required for promotion', 'error');
            $this->redirect("/friend/promote/{$friendId}");
        }
        
        $churchId = (int) $_POST['church_id'];
        $pastorId = (int) $_POST['pastor_id'];
        $coachId = (int) $_POST['coach_id'];
        $mentorId = !empty($_POST['mentor_id']) ? (int) $_POST['mentor_id'] : null;
        
        // Validate that the pastor belongs to the selected church
        $pastor = $this->userModel->findById($pastorId);
        if (!$pastor || $pastor['role'] !== 'pastor' || $pastor['church_id'] != $churchId) {
            flash('Selected pastor does not belong to the selected church', 'error');
            $this->redirect("/friend/promote/{$friendId}");
        }
        
        // Validate that the coach belongs to the selected church
        $coach = $this->userModel->findById($coachId);
        if (!$coach || $coach['role'] !== 'coach' || $coach['church_id'] != $churchId) {
            flash('Selected coach does not belong to the selected church', 'error');
            $this->redirect("/friend/promote/{$friendId}");
        }
        
        // Validate that the mentor belongs to the selected church (if provided)
        if ($mentorId) {
            $mentor = $this->userModel->findById($mentorId);
            if (!$mentor || $mentor['role'] !== 'mentor' || $mentor['church_id'] != $churchId) {
                flash('Selected mentor does not belong to the selected church', 'error');
                $this->redirect("/friend/promote/{$friendId}");
            }
            
            // Validate that the mentor is assigned to the selected coach
            $mentorCoach = $this->userModel->getHierarchyParent($mentorId);
            if (!$mentorCoach || $mentorCoach['id'] != $coachId) {
                flash('Selected mentor is not assigned to the selected coach', 'error');
                $this->redirect("/friend/promote/{$friendId}");
            }
        }
        
        // Update the friend with the new assignments and promote to active
        $data = [
            'church_id' => $churchId,
            'status' => 'active'
        ];
        
        if ($this->userModel->update($friendId, $data)) {
            // Set up the hierarchy: Friend -> Mentor -> Coach
            if ($mentorId) {
                // If mentor is specified, assign friend directly to that mentor
                $this->userModel->updateHierarchyRelationship($friendId, $mentorId);
            } else {
                // If no mentor specified, find a mentor under the selected coach
                $mentors = $this->userModel->getUsersByRoleAndChurch('mentor', $churchId);
                $assignedMentor = null;
                
                foreach ($mentors as $mentor) {
                    $mentorHierarchy = $this->userModel->getHierarchyParent($mentor['id']);
                    if ($mentorHierarchy && $mentorHierarchy['id'] == $coachId) {
                        $assignedMentor = $mentor;
                        break;
                    }
                }
                
                // If no mentor is found under the coach, assign the friend directly to the coach
                if ($assignedMentor) {
                    $this->userModel->updateHierarchyRelationship($friendId, $assignedMentor['id']);
                } else {
                    // Assign directly to coach (this will be handled by the hierarchy system)
                    $this->userModel->updateHierarchyRelationship($friendId, $coachId);
                }
            }
            
            logInfo('Friend promoted to active member', [
                'friend_id' => $friendId,
                'friend_name' => $friend['name'],
                'church_id' => $churchId,
                'pastor_id' => $pastorId,
                'coach_id' => $coachId,
                'mentor_id' => $mentorId,
                'promoted_by' => $_SESSION['user_id']
            ]);
            
            flash('Friend promoted to active member successfully! They will now appear in the regular members list.', 'success');
            $this->redirect('/friend');
        } else {
            flash('Failed to promote friend', 'error');
            $this->redirect("/friend/promote/{$friendId}");
        }
    }
    
    private function getFriendStats($churchId): array
    {
        $totalFriends = $this->userModel->countFriendsByStatus('pending', $churchId);
        $assignedFriends = $this->userModel->countAssignedFriends($churchId);
        $unassignedFriends = $totalFriends - $assignedFriends;
        
        return [
            'total' => $totalFriends,
            'assigned' => $assignedFriends,
            'unassigned' => $unassignedFriends
        ];
    }
}
