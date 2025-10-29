<?php

$builtin_default = 'mistral/mistral-medium-2508';
$builtin_cheap = 'mistral/mistral-medium-2508';
$builtin_default_embeddings = 'openai/' . \Mateffy\Magic\Embeddings\OpenAIEmbeddings::TEXT_EMBEDDING_3_SMALL;


return [
    'models' => [
        'default' => env('LLM_MAGIC_MODEL', $builtin_default),
		'cheap' => env('LLM_MAGIC_CHEAP_MODEL', $builtin_cheap),
		'extraction' => env('LLM_MAGIC_EXTRACTION_MODEL', null),
		'chat' => env('LLM_MAGIC_CHAT_MODEL', null),
		'embeddings' => env('LLM_MAGIC_EMBEDDINGS_MODEL', $builtin_default_embeddings),
    ]
];
