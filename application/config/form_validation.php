<?php
$config = [
    'auth/login' => [
        [
            'field' => 'email',
            'label' => 'Email',
            'rules' => 'required|valid_email',
        ],
        [
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'required',
        ]
    ],
    'users/create' => [
        [
            'field' => 'email',
            'label' => 'Email',
            'rules' => 'required|valid_email|is_unique[users.auth_email]|callback_matchContact',
        ],
        [
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'required|min_length[8]|matches[passconf]',
        ],
        [
            'field' => 'passconf',
            'label' => 'Password Confirmation',
            'rules' => 'required|min_length[8]',
        ],
        [
            'field' => 'ext',
            'label' => 'CudaTel Extension',
            'rules' => 'required|min_length[4]|max_length[4]',
        ],
        [
            'field' => 'pin',
            'label' => 'CudaTel PIN',
            'rules' => 'required|min_length[4]|max_length[4]',
        ]


    ],
    'users/update' => [
        [
            'field' => 'email',
            'label' => 'Email',
            'rules' => 'required|valid_email|callback_matchContact',
        ],
        [
            'field' => 'ext',
            'label' => 'CudaTel Extension',
            'rules' => 'required|min_length[4]|max_length[4]',
        ],
        [
            'field' => 'pin',
            'label' => 'CudaTel PIN',
            'rules' => 'required|min_length[4]|max_length[4]',
        ]
    ],
    'users/reset' => [
        [
            'field' => 'reset_token',
            'label' => 'Token',
            'rules' => 'required|callback_checkResetToken',
        ],
        [
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'required|min_length[8]|matches[passconf]',
        ],
        [
            'field' => 'passconf',
            'label' => 'Password Confirmation',
            'rules' => 'required|min_length[8]',
        ],
    ]
];