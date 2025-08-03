<?php
// Role-based permissions helper

function getUserRole() {
    return $_SESSION['user_role'] ?? 'user';
}

function isAdmin() {
    return getUserRole() === 'admin';
}

function isOwner() {
    return getUserRole() === 'owner';
}

function isUser() {
    return getUserRole() === 'user';
}

function canEdit() {
    return isAdmin();
}

function canDelete() {
    return isAdmin();
}

function canAddPurchase() {
    return isAdmin();
}

function canAddExpense() {
    return isAdmin() || isOwner();
}

function canAddReturn() {
    return isAdmin();
}

function canViewHistory() {
    return isAdmin() || isOwner();
}

function canManageUsers() {
    return isAdmin();
}

function canChangeRoles() {
    return isAdmin();
}

function canExportDatabase() {
    return isAdmin() || isOwner() || isUser();
}

function canImportDatabase() {
    return isAdmin();
}

function canManageDatabase() {
    return isAdmin();
}

function requireRole($requiredRole) {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit();
    }
    
    $userRole = getUserRole();
    
    if ($requiredRole === 'admin' && !isAdmin()) {
        header('Location: index.php');
        exit();
    }
    
    if ($requiredRole === 'owner' && !isOwner() && !isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

function requirePermission($permission) {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit();
    }
    
    switch ($permission) {
        case 'edit':
            if (!canEdit()) {
                header('Location: index.php');
                exit();
            }
            break;
        case 'delete':
            if (!canDelete()) {
                header('Location: index.php');
                exit();
            }
            break;
        case 'add_purchase':
            if (!canAddPurchase()) {
                header('Location: index.php');
                exit();
            }
            break;
        case 'add_expense':
            if (!canAddExpense()) {
                header('Location: index.php');
                exit();
            }
            break;
        case 'add_return':
            if (!canAddReturn()) {
                header('Location: index.php');
                exit();
            }
            break;
        case 'view_history':
            if (!canViewHistory()) {
                header('Location: index.php');
                exit();
            }
            break;
        case 'manage_users':
            if (!canManageUsers()) {
                header('Location: index.php');
                exit();
            }
            break;
        case 'manage_database':
            if (!canManageDatabase()) {
                header('Location: index.php');
                exit();
            }
            break;
    }
}

function getRoleDisplayName($role) {
    switch ($role) {
        case 'admin':
            return 'Administrator';
        case 'owner':
            return 'Owner';
        case 'user':
            return 'User';
        default:
            return 'Unknown';
    }
}

function getRoleColor($role) {
    switch ($role) {
        case 'admin':
            return 'bg-purple-100 text-purple-800';
        case 'owner':
            return 'bg-orange-100 text-orange-800';
        case 'user':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?> 