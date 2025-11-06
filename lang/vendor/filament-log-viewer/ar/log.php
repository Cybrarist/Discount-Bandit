<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'عارض السجلات',
    ],
    'show' => [
        'title' => 'عرض السجل :log',
    ],
    'navigation' => [
        'group' => 'السجلات',
        'label' => 'عارض السجلات',
        'sort'  => 100,
    ],
    'table' => [
        'columns' => [
            'date'    => [
                'label' => 'التاريخ',
            ],
            'level'   => [
                'label' => 'المستوى',
            ],
            'message' => [
                'label' => 'الرسالة',
            ],
            'filename' => [
                'label' => 'اسم الملف',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'عرض',
            ],
            'download' => [
                'label' => 'تحميل السجل :log',
                'bulk'  => [
                    'label' => 'تحميل السجلات',
                    'error' => 'خطأ في تحميل السجلات',
                ],
            ],
            'delete' => [
                'label'   => 'حذف السجل :log',
                'success' => 'تم حذف السجل بنجاح',
                'error'   => 'خطأ في حذف السجل',
                'bulk'    => [
                    'label' => 'حذف السجلات المحددة',
                ],
            ],
            'clear' => [
                'label' => 'مسح السجل :log',
                'success' => 'تم مسح السجل بنجاح',
                'error' => 'حدث خطأ أثناء مسح السجل',
                'bulk' => [
                    'success' => 'تم مسح السجلات بنجاح',
                    'label' => 'مسح السجلات المحددة',
                ],
            ],
            'close' => [
                'label' => 'رجوع',
            ],
        ],
        'detail' => [
            'title'         => 'التفاصيل',
            'file_path'     => 'مسار الملف',
            'log_entries'   => 'المداخل',
            'size'          => 'الحجم',
            'created_at'    => 'تاريخ الإنشاء',
            'updated_at'    => 'آخر تعديل',
        ],
    ],
    'levels' => [
        'all'       => 'الكل',
        'emergency' => 'حالة طوارئ',
        'alert'     => 'تنبيه',
        'critical'  => 'حرج',
        'error'     => 'خطأ',
        'warning'   => 'تحذير',
        'notice'    => 'إشعار',
        'info'      => 'معلومات',
        'debug'     => 'تصحيح الأخطاء',
    ],
];
