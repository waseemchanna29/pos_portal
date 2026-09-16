# Sprint 1 — Setup Notes

## 1. Register the new middleware

Laravel 12 doesn't use `app/Http/Kernel.php` by default — middleware aliases go
in `bootstrap/app.php`. Add these two lines inside the `->withMiddleware()`
callback:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'subscription.active' => \App\Http\Middleware\EnsureSubscriptionActive::class,
        'module'               => \App\Http\Middleware\EnsureModuleEnabled::class,
        // ...keep any existing aliases (auth, superadmin, admin, salesman) here too
    ]);
})
```

## 2. Apply `subscription.active` to the existing role groups

In `routes/web.php`, add `subscription.active` to the admin and salesman
middleware groups (never to superadmin — it's exempt):

```php
Route::middleware(['auth', 'admin', 'subscription.active'])->prefix('admin')->...
Route::middleware(['auth', 'salesman', 'subscription.active'])->prefix('salesman')->...
```

(The `booker` group will be added in Sprint 3 with the same pattern, plus
Sanctum for the API.)

## 3. New routes — add inside the existing superadmin group in `routes/web.php`

```php
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {

    // ...existing dashboard / outlets / assign-admin / available-admins routes stay as-is...

    // Subscription actions
    Route::post('/outlets/{outlet}/mark-paid',     [OutletController::class, 'markPaid'])->name('outlets.mark-paid');
    Route::post('/outlets/{outlet}/change-plan',   [OutletController::class, 'changePlan'])->name('outlets.change-plan');
    Route::post('/outlets/{outlet}/terminate',     [OutletController::class, 'terminate'])->name('outlets.terminate');
    Route::post('/outlets/{outlet}/reactivate',    [OutletController::class, 'reactivate'])->name('outlets.reactivate');

    // Module toggles
    Route::post('/outlets/{outlet}/toggle-module', [OutletController::class, 'toggleModule'])->name('outlets.toggle-module');
});
```

## 4. Run migrations

```bash
php artisan migrate
```

Runs in order automatically due to the timestamp prefixes:
1. `..._add_subscription_fields_to_outlets_table`
2. `..._create_shop_modules_table`
3. `..._create_subscription_logs_table`
4. `..._add_booker_role_and_soft_deletes_to_users_table`

## 5. Scheduled task for trial expiry (optional but recommended)

`SubscriptionService::expireOverdueTrials()` needs to run daily. Add an
Artisan command wrapping it, then in `routes/console.php`:

```php
Schedule::command('subscriptions:expire-trials')->daily();
```

(Command class not included in this sprint's files — flag if you want it
built now or later; it's a 10-line wrapper around the existing service
method, low priority vs. the rest of the foundation.)

## 6. What this sprint does NOT yet touch

- Views (`resources/views/superadmin/outlets/*`) — the controller now expects
  `admin_name`, `admin_email`, `admin_password`, `plan_type`,
  `trial_ends_at`, `monthly_amount`, `max_salesmen`, `max_bookers` fields in
  the create form, and a module-toggle UI + subscription action buttons on
  the show page. Not built yet — say the word and I'll do the Blade views
  next, or you can wire your own against these routes/controller in the
  meantime.
- Salesman/booker-specific permission enforcement (Sprint 2/3).
- `UserController`/`SalesmanController` limit checks against
  `max_salesmen`/`max_bookers` when creating staff — small addition, goes in
  at the start of Sprint 2 since that's where salesman creation logic lives.
