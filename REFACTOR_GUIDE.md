# Refactor Guide (Incremental MVC)

This project now uses a clean entrypoint pattern on:
- `index.php`
- `account_executive.php`

Each entrypoint should only instantiate a controller and call one action.

## Current Pattern

1. Route/entrypoint file:
- `index.php`
- `account_executive.php`

2. Controller:
- `controllers/DashboardController.php`
- `controllers/AccountExecutiveController.php`

3. Model:
- `models/DashboardModel.php`
- `models/AccountExecutiveModel.php`

4. View:
- `views/dashboard/index.php`
- `views/account_executive/index.php`

5. Shared layout:
- `views/layouts/header.php`
- `views/layouts/footer.php`

## Best-Practice Rules

- Keep PHP pages at project root as thin entrypoints only.
- Move data shaping/business logic to models/services.
- Keep controllers thin: validate request, call model, pass data to view.
- Keep views presentation-only. No random data generation in views.
- Escape dynamic output with `htmlspecialchars` by default.
- Reuse shared layout and asset loading through the base controller.

## How to Migrate Remaining Pages

For each page (`crm.php`, `facilities.php`, `passport_visa.php`, `schedule_rates.php`):

1. Create `models/<PageName>Model.php`.
2. Create `controllers/<PageName>Controller.php`.
3. Create `views/<page_name>/index.php`.
4. Replace root `<page>.php` with a thin controller call.
5. Move inline functions/random/mock generation into the model.
6. Keep existing JS/CSS by passing assets via controller `render(...)`.

## Optional Next Hardening Steps

- Add a front controller (`public/index.php`) and route map.
- Add dependency injection container for models/services.
- Add a request/response abstraction layer.
- Add unit tests for models and integration tests for controllers.
