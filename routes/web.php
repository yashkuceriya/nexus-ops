<?php

use App\Livewire\ApiDocs;
use App\Livewire\AssetDetail;
use App\Livewire\AssetHealthMatrix;
use App\Livewire\AssetList;
use App\Livewire\AuditLogViewer;
use App\Livewire\AutomationRuleBuilder;
use App\Livewire\AutomationRuleList;
use App\Livewire\FacilityMap;
use App\Livewire\FloorPlan;
use App\Livewire\OccupantRequestForm;
use App\Livewire\PortfolioDashboard;
use App\Livewire\ProjectDetail;
use App\Livewire\ProjectList;
use App\Livewire\ReportsPage;
use App\Livewire\RequestTracker;
use App\Livewire\SensorDashboard;
use App\Livewire\VendorDetail;
use App\Livewire\VendorList;
use App\Livewire\WorkOrderDetail;
use App\Livewire\WorkOrderList;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/login', function () {
    $credentials = request()->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (auth()->attempt($credentials)) {
        request()->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
})->middleware(['guest', 'throttle:auth']);

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

Route::get('/health', function () {
    return response('OK', 200);
});

// Public occupant request routes (no auth required)
Route::get('/request', OccupantRequestForm::class)->middleware('throttle:public-form')->name('request.create');
Route::get('/request/{token}', RequestTracker::class)->name('request.track');

Route::middleware(['auth', 'tenant.active'])->group(function () {
    Route::get('/dashboard', PortfolioDashboard::class)->name('dashboard');
    Route::get('/projects', ProjectList::class)->name('projects.index');
    Route::get('/projects/{id}', ProjectDetail::class)->name('projects.show');
    Route::get('/work-orders', WorkOrderList::class)->name('work-orders.index');
    Route::get('/work-orders/{id}', WorkOrderDetail::class)->name('work-orders.show');
    Route::get('/assets', AssetList::class)->name('assets.index');
    Route::get('/assets/{id}', AssetDetail::class)->name('assets.show');
    Route::get('/sensors', SensorDashboard::class)->name('sensors.index');
    Route::get('/reports', ReportsPage::class)->name('reports.index');
    Route::get('/audit-log', AuditLogViewer::class)->name('audit-log.index');
    Route::get('/floor-plan', FloorPlan::class)->name('floor-plan.index');
    Route::get('/health-matrix', AssetHealthMatrix::class)->name('health-matrix.index');
    Route::get('/vendors', VendorList::class)->name('vendors.index');
    Route::get('/vendors/{id}', VendorDetail::class)->name('vendors.show');
    Route::get('/automation', AutomationRuleList::class)->name('automation.index');
    Route::get('/automation/create', AutomationRuleBuilder::class)->name('automation.create');
    Route::get('/automation/{id}/edit', AutomationRuleBuilder::class)->name('automation.edit');
    Route::get('/map', FacilityMap::class)->name('map.index');
    Route::get('/docs', ApiDocs::class)->name('docs.index');
});
