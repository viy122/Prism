<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_office_head_pages_require_login(): void
    {
        $this->get('/office-head')->assertRedirect(route('login'));
        $this->get('/office-head/budget-proposal')->assertRedirect(route('login'));
        $this->get('/office-head/my-proposals')->assertRedirect(route('login'));
        $this->get('/office-head/purchase-requests')->assertRedirect(route('login'));
        $this->get('/office-head/office-assets')->assertRedirect(route('login'));
        $this->post('/office-assets/update', [])->assertRedirect(route('login'));
    }

    public function test_user_switch_destinations_require_login(): void
    {
        $this->get('/finance-office')->assertRedirect(route('login'));
        $this->get('/procurement-office')->assertRedirect(route('login'));
        $this->get('/chancellor')->assertRedirect(route('login'));
        $this->get('/vice-chancellor')->assertRedirect(route('login'));
    }

    public function test_finance_office_pages_require_login(): void
    {
        $this->get('/finance-office')->assertRedirect(route('login'));
        $this->get('/finance-office/proposal-review')->assertRedirect(route('login'));
        $this->get('/finance-office/proposal-review/eng-2027-main')->assertRedirect(route('login'));
        // APP is a Procurement page; this obsolete Finance URL does not exist.
        $this->get('/finance-office/annual-procurement-plan')->assertNotFound();
        $this->get('/finance-office/budget-utilization-report')->assertRedirect(route('login'));
    }

    public function test_procurement_office_pages_require_login(): void
    {
        $this->get('/procurement-office')->assertRedirect(route('login'));
        $this->get('/procurement-office/purchase-request-management')->assertRedirect(route('login'));
        $this->get('/procurement-office/procurement-reports')->assertRedirect(route('login'));
    }

    public function test_chancellor_pages_require_login(): void
    {
        $this->get('/chancellor')->assertRedirect(route('login'));
        $this->get('/chancellor/budget-approval')->assertRedirect(route('login'));
        $this->get('/chancellor/procurement-reports')->assertRedirect(route('login'));
    }

    public function test_vice_chancellor_pages_require_login(): void
    {
        $this->get('/vice-chancellor')->assertRedirect(route('login'));
        $this->get('/vice-chancellor/division-procurement-status')->assertRedirect(route('login'));
        $this->get('/vice-chancellor/division-performance-report')->assertRedirect(route('login'));
    }
}
