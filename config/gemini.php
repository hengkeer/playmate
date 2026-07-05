<?php

return [

    'api_key'          => env('GEMINI_API_KEY'),
    'base_url'         => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
    'embedding_model'  => env('GEMINI_EMBEDDING_MODEL', 'gemini-embedding-001'),
    'chat_model'       => env('GEMINI_CHAT_MODEL', 'gemini-2.0-flash'),

    'top_k'             => 5,
    'max_context_chars' => 6000,
    'history_window'    => 10,
    'embed_dim'         => 768,

    'rag_index_path'  => storage_path('app/private/rag'),
];
