<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminErrorHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register dummy test routes that trigger exceptions
        Route::middleware(['web'])->group(function () {
            Route::get('/admin/test-error-endpoint', function () {
                throw new \RuntimeException('Simulated admin server failure for testing');
            })->name('admin-test-error');

            Route::get('/non-admin/test-error', function () {
                throw new \RuntimeException('Simulated non-admin failure');
            });
        });
    }

    public function test_mdi_partial_request_returns_error_pane_with_headers_and_retry_button()
    {
        config(['app.debug' => true]);

        $response = $this->withHeaders([
            'X-MDI-Partial' => '1',
        ])->get('/admin/test-error-endpoint');

        $response->assertStatus(500);
        $response->assertHeader('X-POS-Error', '1');
        $this->assertNotEmpty($response->headers->get('X-POS-Error-ID'));

        $content = $response->getContent();
        $this->assertStringContainsString('workspace-error-card', $content);
        $this->assertStringContainsString('mdi-retry-btn', $content);
        $this->assertStringContainsString('window.MDI.retryTab', $content);
        $this->assertStringContainsString('Developer Debugging Information', $content);
        $this->assertStringContainsString('Simulated admin server failure for testing', $content);
    }

    public function test_full_page_browser_refresh_renders_within_workspace_layout()
    {
        config(['app.debug' => true]);

        $response = $this->get('/admin/test-error-endpoint');

        $response->assertStatus(500);
        $response->assertHeader('X-POS-Error', '1');
        $this->assertNotEmpty($response->headers->get('X-POS-Error-ID'));

        $content = $response->getContent();
        // Layout components preserved
        $this->assertStringContainsString('workspace-viewport', $content);
        $this->assertStringContainsString('workspace-tab-bar', $content);
        // Error card preserved
        $this->assertStringContainsString('workspace-error-card', $content);
        $this->assertStringContainsString('mdi-retry-btn', $content);
    }

    public function test_production_mode_hides_raw_developer_details()
    {
        config(['app.debug' => false]);

        $response = $this->withHeaders([
            'X-MDI-Partial' => '1',
        ])->get('/admin/test-error-endpoint');

        $response->assertStatus(500);
        $response->assertHeader('X-POS-Error', '1');
        $errorId = $response->headers->get('X-POS-Error-ID');
        $this->assertNotEmpty($errorId);

        $content = $response->getContent();
        // Friendly card & error reference are present
        $this->assertStringContainsString('workspace-error-card', $content);
        $this->assertStringContainsString($errorId, $content);
        $this->assertStringContainsString('mdi-retry-btn', $content);

        // Technical details & stack trace are NOT present
        $this->assertStringNotContainsString('Developer Debugging Information', $content);
        $this->assertStringNotContainsString('Stack Trace Snippet', $content);
        $this->assertStringNotContainsString('Simulated admin server failure for testing', $content);
    }

    public function test_json_requests_receive_structured_error_payload()
    {
        config(['app.debug' => true]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->get('/admin/test-error-endpoint');

        $response->assertStatus(500);
        $response->assertHeader('X-POS-Error', '1');
        $response->assertJsonStructure([
            'success',
            'error_id',
            'message',
            'debug' => [
                'error_id',
                'message',
                'exception',
                'file',
                'line',
                'url',
                'method',
                'trace',
            ],
        ]);
        $response->assertJson([
            'success' => false,
            'message' => 'Simulated admin server failure for testing',
        ]);
    }
}
