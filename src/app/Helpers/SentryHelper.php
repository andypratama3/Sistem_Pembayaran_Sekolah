<?php

/**
 * Sentry Helper Functions
 *
 * Convenient helper functions for Sentry integration throughout the application
 */

use App\Services\SentryService;
use Sentry\Laravel\Facade as Sentry;

if (! function_exists('sentry_log')) {
    /**
     * Log a message to Sentry
     *
     * @param  string  $message  The message to log
     * @param  string  $level  The log level (info, warning, error, debug, fatal)
     * @param  array  $context  Additional context data
     */
    function sentry_log(string $message, string $level = 'info', array $context = []): void
    {
        SentryService::captureMessage($message, $level, $context);
    }
}

if (! function_exists('sentry_error')) {
    /**
     * Log an error to Sentry
     *
     * @param  Throwable  $exception  The exception to log
     * @param  array  $context  Additional context data
     */
    function sentry_error(Throwable $exception, array $context = []): void
    {
        SentryService::captureException($exception, $context);
    }
}

if (! function_exists('sentry_breadcrumb')) {
    /**
     * Add a breadcrumb to Sentry
     *
     * @param  string  $message  The breadcrumb message
     * @param  string  $category  The breadcrumb category
     * @param  string  $level  The breadcrumb level
     * @param  array  $data  Additional data
     */
    function sentry_breadcrumb(string $message, string $category = 'app', string $level = 'info', array $data = []): void
    {
        SentryService::addBreadcrumb($message, $category, $level, $data);
    }
}

if (! function_exists('sentry_user')) {
    /**
     * Set the current user context in Sentry
     *
     * @param  int|string  $userId  The user ID
     * @param  string  $email  The user email
     * @param  string  $username  The username
     * @param  array  $extraData  Extra user data
     */
    function sentry_user(int|string $userId, string $email = '', string $username = '', array $extraData = []): void
    {
        SentryService::setUserContext($userId, $email, $username, $extraData);
    }
}

if (! function_exists('sentry_clear_user')) {
    /**
     * Clear the user context from Sentry
     */
    function sentry_clear_user(): void
    {
        SentryService::clearUserContext();
    }
}

if (! function_exists('sentry_tags')) {
    /**
     * Add tags to Sentry
     *
     * @param  array  $tags  Tags to add
     */
    function sentry_tags(array $tags): void
    {
        SentryService::addTags($tags);
    }
}

if (! function_exists('sentry_context')) {
    /**
     * Add context to Sentry
     *
     * @param  string  $contextName  The context name
     * @param  array  $data  The context data
     */
    function sentry_context(string $contextName, array $data): void
    {
        SentryService::addContext($contextName, $data);
    }
}

if (! function_exists('sentry_extra')) {
    /**
     * Add extra data to Sentry
     *
     * @param  string  $key  The data key
     * @param  mixed  $value  The data value
     */
    function sentry_extra(string $key, $value): void
    {
        SentryService::addExtra($key, $value);
    }
}

if (! function_exists('sentry_db_query')) {
    /**
     * Log a database query to Sentry
     *
     * @param  string  $query  The SQL query
     * @param  float  $duration  The query duration in milliseconds
     * @param  bool  $isSlowQuery  Whether to log as slow query
     */
    function sentry_db_query(string $query, float $duration, bool $isSlowQuery = false): void
    {
        SentryService::logDatabaseQuery($query, $duration, $isSlowQuery);
    }
}

if (! function_exists('sentry_api_call')) {
    /**
     * Log an API call to Sentry
     *
     * @param  string  $method  The HTTP method
     * @param  string  $endpoint  The endpoint URL
     * @param  int  $statusCode  The response status code
     * @param  float  $duration  The call duration in milliseconds
     * @param  array  $additionalData  Additional data
     */
    function sentry_api_call(string $method, string $endpoint, int $statusCode, float $duration, array $additionalData = []): void
    {
        SentryService::logApiCall($method, $endpoint, $statusCode, $duration, $additionalData);
    }
}

if (! function_exists('sentry_queue_job')) {
    /**
     * Log a queue job to Sentry
     *
     * @param  string  $jobName  The job name
     * @param  string  $status  The job status (success, failed, etc)
     * @param  float  $duration  The job duration in milliseconds
     * @param  array  $additionalData  Additional data
     */
    function sentry_queue_job(string $jobName, string $status, float $duration, array $additionalData = []): void
    {
        SentryService::logQueueJob($jobName, $status, $duration, $additionalData);
    }
}

if (! function_exists('sentry_auth_event')) {
    /**
     * Log an authentication event to Sentry
     *
     * @param  string  $eventType  The event type (login, logout, failed_login, etc)
     * @param  int|string  $userId  The user ID
     * @param  array  $additionalData  Additional data
     */
    function sentry_auth_event(string $eventType, $userId = null, array $additionalData = []): void
    {
        SentryService::logAuthEvent($eventType, $userId, $additionalData);
    }
}
