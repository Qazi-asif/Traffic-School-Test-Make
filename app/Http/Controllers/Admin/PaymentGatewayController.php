<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\PaymentGatewayConfigService;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function __construct(protected PaymentGatewayConfigService $gatewayService) {}

    public function index()
    {
        $gateways = PaymentGateway::with(['settings'])->ordered()->get();

        return view('admin.payment-gateways.index', compact('gateways'));
    }

    public function show(PaymentGateway $gateway)
    {
        $gateway->load(['settings', 'webhooks']);
        $settingsSchema = $this->gatewayService->getSettingsSchema($gateway->code);
        $testSettings = $gateway->getAllSettings('test');
        $productionSettings = $gateway->getAllSettings('production');
        $validation = $this->gatewayService->validateGatewaySettings($gateway);

        return view('admin.payment-gateways.show', compact(
            'gateway',
            'settingsSchema',
            'testSettings',
            'productionSettings',
            'validation'
        ));
    }

    public function edit(PaymentGateway $gateway)
    {
        $settingsSchema = $this->gatewayService->getSettingsSchema($gateway->code);
        $testSettings = $gateway->getAllSettings('test')->pluck('setting_value', 'setting_key');
        $productionSettings = $gateway->getAllSettings('production')->pluck('setting_value', 'setting_key');

        return view('admin.payment-gateways.edit', compact(
            'gateway',
            'settingsSchema',
            'testSettings',
            'productionSettings'
        ));
    }

    public function update(Request $request, PaymentGateway $gateway)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'integer|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'transaction_fee_percent' => 'nullable|numeric|min:0|max:100',
            'transaction_fee_fixed' => 'nullable|numeric|min:0',
        ]);

        $oldValues = $gateway->only(array_keys($validated));
        $gateway->update($validated);
        $gateway->logAction('updated', $oldValues, $validated);

        return redirect()
            ->route('admin.payment-gateways.show', $gateway)
            ->with('success', 'Gateway updated successfully');
    }

    public function updateSettings(Request $request, PaymentGateway $gateway)
    {
        $environment = $request->input('environment', 'test');
        $settings = $request->input('settings', []);

        $settings = array_filter($settings, fn ($value) => $value !== '' && $value !== null);

        $this->gatewayService->updateSettings($gateway, $settings, $environment);

        return redirect()
            ->route('admin.payment-gateways.show', $gateway)
            ->with('success', ucfirst($environment).' settings updated successfully');
    }

    public function testConnection(PaymentGateway $gateway)
    {
        $result = $this->gatewayService->testConnection($gateway);
        $gateway->logAction('test_connection', null, $result);

        return response()->json($result);
    }

    public function activate(PaymentGateway $gateway)
    {
        $result = $this->gatewayService->activateGateway($gateway);

        if ($result['success']) {
            return redirect()
                ->route('admin.payment-gateways.index')
                ->with('success', $result['message']);
        }

        return redirect()
            ->route('admin.payment-gateways.show', $gateway)
            ->with('error', $result['message']);
    }

    public function deactivate(PaymentGateway $gateway)
    {
        $this->gatewayService->deactivateGateway($gateway);

        return redirect()
            ->route('admin.payment-gateways.index')
            ->with('success', 'Gateway deactivated');
    }

    public function toggleMode(PaymentGateway $gateway)
    {
        $this->gatewayService->toggleMode($gateway);
        $mode = $gateway->fresh()->is_test_mode ? 'Test' : 'Production';

        return redirect()
            ->route('admin.payment-gateways.show', $gateway)
            ->with('success', "Switched to {$mode} mode");
    }

    public function logs(PaymentGateway $gateway)
    {
        $logs = $gateway->logs()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.payment-gateways.logs', compact('gateway', 'logs'));
    }

    public function reorder(Request $request)
    {
        $order = $request->input('order', []);

        foreach ($order as $index => $gatewayId) {
            PaymentGateway::where('id', $gatewayId)->update(['display_order' => $index]);
        }

        $this->gatewayService->clearCache();

        return response()->json(['success' => true]);
    }
}
