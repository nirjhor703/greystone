<?php

return [
    'root_promotion_passcode' => env(
        'ROOT_ADMIN_PROMOTION_PASSCODE',
        'NajmusSakib1234'
    ),

    'modules' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'icon' => 'fa-gauge-high',
            'route' => 'admin.dashboard',
        ],
        'brands' => [
            'label' => 'Brands',
            'icon' => 'fa-store',
            'route' => 'admin.brands.index',
        ],
        'categories' => [
            'label' => 'Categories',
            'icon' => 'fa-layer-group',
            'route' => 'admin.categories.index',
        ],
        'products' => [
            'label' => 'Products',
            'icon' => 'fa-shirt',
            'route' => 'admin.products.index',
        ],
        'orders' => [
            'label' => 'Orders',
            'icon' => 'fa-receipt',
            'route' => 'admin.orders.index',
        ],
        'coupons' => [
            'label' => 'Coupons',
            'icon' => 'fa-ticket',
            'route' => 'admin.coupons.index',
        ],
        'notifications' => [
            'label' => 'Notifications',
            'icon' => 'fa-bell',
            'route' => 'admin.notifications.index',
            'params' => ['category' => 'main'],
        ],
        'stock_notifications' => [
            'label' => 'Stock Notifications',
            'icon' => 'fa-box-open',
            'route' => 'admin.notifications.index',
            'params' => ['category' => 'stock'],
        ],
        'customers' => [
            'label' => 'Customers',
            'icon' => 'fa-users',
            'route' => 'admin.customers.index',
        ],
        'sweet_cool' => [
            'label' => 'Sweet Cool',
            'icon' => 'fa-industry',
            'route' => 'admin.sweet-cool.index',
        ],
        'reports' => [
            'label' => 'Reports',
            'icon' => 'fa-chart-line',
            'route' => 'admin.reports.index',
        ],
        'settings' => [
            'label' => 'Settings',
            'icon' => 'fa-gear',
            'route' => 'admin.settings.index',
        ],
        'admin_users' => [
            'label' => 'Admins',
            'icon' => 'fa-user-shield',
            'route' => 'admin.admin-users.index',
        ],
    ],

    'actions' => [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
    ],

    'sensitive' => [
        'admin_users.manage_admins' => 'Manage Admins',
        'admin_users.manage_super_admins' => 'Manage Super Admins',
        'admin_users.delete_super_admins' => 'Delete Super Admins',
        'admin_users.assign_permissions' => 'Assign Permissions',
        'orders.send_steadfast' => 'Send Orders to Steadfast',
        'reports.export' => 'Export Reports',
    ],
];
