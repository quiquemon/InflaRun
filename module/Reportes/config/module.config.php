<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Reportes\Controller\Index' => 'Reportes\Controller\IndexController',
        ),
    ),
    'router'=>array(
        'routes'=>array(
            'trabajo'=>array(
                 'type'=>'Segment',
                    'options'=>array(
                        'route' => '/reportes[/[:action[/:id[/:name[/:idL]]]]]',
                        'constraints' => array(
                                'action'  =>  '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'  =>  '[0-9-a-z]*',
                                'name'  =>  '[a-z0-‌​9._\~#&=;%+?-]*',
                                'idL'  =>  '[0-9-a-z]*',
                        ),
                        
                        'defaults'  =>  array(
                                'controller' => 'Reportes\Controller\Index',
                                'action'     => 'index'
                         
                        ),
                    ),
           ),
       ),
    ),
    //Cargamos el view manager
   'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'sendinblue/index/index' => __DIR__ . '/../view/Reportes/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
          'sendinblue' =>  __DIR__ . '/../view',
        ),
    ), 
);
