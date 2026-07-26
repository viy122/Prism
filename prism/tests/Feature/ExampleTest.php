<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_office_head_pages_return_successful_responses(): void
    {
        $this->get('/office-head')->assertStatus(200);
        $this->get('/office-head/budget-proposal')->assertStatus(200);
        $this->get('/office-head/my-proposals')->assertStatus(200);
        $this->get('/office-head/purchase-requests')->assertStatus(200);
    }

    public function test_user_switch_destinations_return_successful_responses(): void
    {
        $this->get('/finance-office')->assertStatus(200);
        $this->get('/procurement-office')->assertStatus(200);
        $this->get('/chancellor')->assertStatus(200);
        $this->get('/vice-chancellor')->assertStatus(200);
    }

    public function test_finance_office_pages_return_successful_responses(): void
    {
        $this->get('/finance-office')->assertStatus(200);
        $this->get('/finance-office/proposal-review')->assertStatus(200);
        $this->get('/finance-office/proposal-review/eng-2027-main')->assertStatus(200);
        $this->get('/finance-office/annual-procurement-plan')->assertStatus(200);
        $this->get('/finance-office/budget-utilization-report')->assertStatus(200);
    }

    public function test_procurement_office_pages_return_successful_responses(): void
    {
        $this->get('/procurement-office')->assertStatus(200);
        $this->get('/procurement-office/purchase-request-management')->assertStatus(200);
        $this->get('/procurement-office/procurement-reports')->assertStatus(200);
    }

    public function test_chancellor_pages_return_successful_responses(): void
    {
        $this->get('/chancellor')->assertStatus(200);
        $this->get('/chancellor/budget-approval')->assertStatus(200);
        $this->get('/chancellor/procurement-reports')->assertStatus(200);
    }

    public function test_vice_chancellor_pages_return_successful_responses(): void
    {
        $this->get('/vice-chancellor')->assertStatus(200);
        $this->get('/vice-chancellor/division-procurement-status')->assertStatus(200);
        $this->get('/vice-chancellor/division-performance-report')->assertStatus(200);
    }
}
