<?php

// test('example', function () {
//     $response = $this->get('/');

//     $response->assertStatus(200);
// });

// <?php

// namespace Tests\Feature\Project;

// use Tests\TestCase;
// use App\Models\User;
// use App\Models\Phase;
// use App\Models\Agency;
// use App\Models\Project;
// use App\Models\Customer;
// use App\Models\AgencyEmployee;
// use App\Models\CustomerEmployee;
// use Database\Seeders\ActionSeed;
// use Tests\Traits\AssertJsonForbidden;

// class CreatePhaseTest extends TestCase
// {
//     use AssertJsonForbidden;

//     private CustomerEmployee $customer_employee;
//     private Project $project;
//     private array $body;

//     public function setUp(): void
//     {
//         parent::setUp();

//         $this->seed([ActionSeed::class]);

//         $this->customer_employee = CustomerEmployee::factory()->withActions(['create-update-project'])->create();
//         $this->project = Project::factory()->create(
//             ['customer_uuid' => $this->customer_employee->customer_uuid]
//         );
//         $this->body = Phase::factory()->for($this->project)->make()->toArray();
//     }

//     public function testNonAdminUserCannotCreateAnPhase(): void
//     {
//         $non_admin_user = User::factory()->create();

//         $this->actingAs($non_admin_user, self::AUTH_GUARD)
//             ->postJson($this->uri($this->project), $this->body)
//             ->assertForbidden()
//             ->assertJson([]);

//         $this->assertDatabaseMissing(Phase::class, $this->body);
//     }

//     public function testAdminUserCanCreateAnPhase(): void
//     {
//         $admin_user = User::factory()->admin()->create();

//         $this->actingAs($admin_user, self::AUTH_GUARD)
//             ->postJson($this->uri($this->project), $this->body)
//             ->assertCreated()
//             ->assertJson($this->body)
//             ->assertJsonStructure($this->expectJsonStructure());

//         $this->assertDatabaseHas(Phase::class, [
//             ...$this->body,
//             'project_uuid' => $this->project->uuid,
//         ]);
//     }

//     public function testCustomerEmployeeCanCreateAnPhase(): void
//     {
//         $this->actingAs($this->customer_employee->user, self::AUTH_GUARD)
//             ->postJson($this->uri($this->project), $this->body)
//             ->assertCreated()
//             ->assertJson($this->body)
//             ->assertJsonStructure($this->expectJsonStructure());

//         $this->assertDatabaseHas(Phase::class, [
//             ...$this->body,
//             'project_uuid' => $this->project->uuid,
//         ]);
//     }

//     public function testAgencyEmployeeCanCreateAnPhase(): void
//     {
//         $agency = Agency::factory()->create();

//         $agency_employee = AgencyEmployee::factory()->create([
//             'agency_uuid' => $agency->uuid,
//         ]);

//         $customer = Customer::factory()->create([
//             'agency_uuid' => $agency->uuid,
//         ]);

//         $project = Project::factory()->create([
//             'active' => true,
//             'customer_uuid' => $customer->uuid,
//         ]);

//         $this->body = Phase::factory()->for($project)->make()->toArray();

//         $this->actingAs($agency_employee->user, self::AUTH_GUARD)
//             ->postJson($this->uri($project), $this->body)
//             ->assertCreated()
//             ->assertJson($this->body)
//             ->assertJsonStructure($this->expectJsonStructure());

//         $this->assertDatabaseHas(Phase::class, [
//             ...$this->body,
//             'project_uuid' => $project->uuid,
//         ]);
//     }

//     public function testCustomerEmployeeCannotCreateAnPhaseWithoutPermission(): void
//     {
//         $this->customer_employee->user->customerEmployee->profile->actions()
//             ->where('slug', 'create-update-project')
//             ->delete();

//         $this->actingAs($this->customer_employee->user, self::AUTH_GUARD)
//             ->postJson($this->uri($this->project), $this->body)
//             ->assertForbidden()
//             ->assertJson($this->jsonForbidden());
//     }

//     private function uri(Project $project): string
//     {
//         return route('v1.customers.phases.store', ['project' => $project->uuid]);
//     }

//     protected function expectJsonStructure(): array
//     {
//         return [
//             'uuid',
//             'project_uuid',
//             'name',
//             'code',
//             'start_at',
//             'end_at',
//             'budget',
//             'alert',
//         ];
//     }
// }