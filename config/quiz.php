<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Quiz System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the quiz system,
    | including table preferences and legacy support settings.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Disable Legacy Questions Table
    |--------------------------------------------------------------------------
    |
    | When set to true, the system will ONLY use the 'chapter_questions' table
    | and completely ignore the legacy 'questions' table. This prevents any
    | potential duplicates and ensures consistent behavior.
    |
    | Set to false to maintain backward compatibility with legacy questions.
    |
    */

    'disable_legacy_questions_table' => env('DISABLE_LEGACY_QUESTIONS_TABLE', false),

    /*
    |--------------------------------------------------------------------------
    | Primary Questions Table
    |--------------------------------------------------------------------------
    |
    | The primary table to use for storing and retrieving quiz questions.
    | This should always be 'chapter_questions' for new installations.
    |
    */

    'primary_questions_table' => 'chapter_questions',

    /*
    |--------------------------------------------------------------------------
    | Legacy Questions Table
    |--------------------------------------------------------------------------
    |
    | The legacy table that may contain old quiz questions.
    | This is only used when disable_legacy_questions_table is false.
    |
    */

    'legacy_questions_table' => 'questions',

    /*
    |--------------------------------------------------------------------------
    | Quiz Logging
    |--------------------------------------------------------------------------
    |
    | Enable detailed logging for quiz operations to help with debugging
    | and monitoring quiz performance.
    |
    */

    'enable_quiz_logging' => env('QUIZ_LOGGING_ENABLED', true),

];