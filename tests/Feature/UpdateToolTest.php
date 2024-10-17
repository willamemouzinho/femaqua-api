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

// class UpdatePhaseTest extends TestCase
// {
//     use AssertJsonForbidden;

//     private CustomerEmployee $customer_employee;
//     private Phase $phase;
//     private array $body;

//     public function setUp(): void
//     {
//         parent::setUp();

//         $this->seed([ActionSeed::class]);

//         $this->customer_employee = CustomerEmployee::factory()->withActions(['create-update-project'])->create();
//         $project = Project::factory()->create(
//             ['customer_uuid' => $this->customer_employee->customer_uuid]
//         );
//         $this->phase = Phase::factory()->for($project)->create();
//         $this->body = Phase::factory()->for($project)->make()->toArray();
//     }

//     public function testAdminUserCanUpdateAnPhase(): void
//     {
//         $admin_user = User::factory()->admin()->create();

//         $this->actingAs($admin_user, self::AUTH_GUARD)
//             ->putJson($this->uri($this->phase), $this->body)
//             ->assertOk()
//             ->assertJsonFragment($this->body)
//             ->assertJsonStructure($this->expectedJsonStructure());

//         $this->assertDatabaseHas(Phase::class, [
//             'uuid' => $this->phase->uuid,
//             ...$this->body
//         ]);
//     }

//     public function testNonAdminUserCannotUpdateAnPhase(): void
//     {
//         $non_admin_user = User::factory()->create();

//         $this->actingAs($non_admin_user, self::AUTH_GUARD)
//             ->putJson($this->uri($this->phase), $this->body)
//             ->assertForbidden();

//         $this->assertDatabaseMissing(Phase::class, $this->body);
//     }

//     public function testAgencyEmployeeCanUpdateAnPhase(): void
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

//         $this->phase = Phase::factory()->for($project)->create();

//         $this->body = Phase::factory()->for($project)->make()->toArray();

//         $this->actingAs($agency_employee->user, self::AUTH_GUARD)
//             ->putJson($this->uri($this->phase), $this->body)
//             ->assertOk()
//             ->assertJsonFragment($this->body)
//             ->assertJsonStructure($this->expectedJsonStructure());

//         $this->assertDatabaseHas(Phase::class, [
//             'uuid' => $this->phase->uuid,
//             ...$this->body
//         ]);
//     }

//     public function testCustomerEmployeeCanUpdateAnPhase(): void
//     {
//         $this->actingAs($this->customer_employee->user, self::AUTH_GUARD)
//             ->putJson($this->uri($this->phase), $this->body)
//             ->assertOk()
//             ->assertJsonFragment($this->body)
//             ->assertJsonStructure($this->expectedJsonStructure());

//         $this->assertDatabaseHas(Phase::class, [
//             'uuid' => $this->phase->uuid,
//             ...$this->body
//         ]);
//     }

//     public function testCannotUpdateAnPhaseWithoutPermission(): void
//     {
//         $this->customer_employee->user->customerEmployee->profile->actions()
//             ->where('slug', 'create-update-project')
//             ->delete();

//         $this->actingAs($this->customer_employee->user, self::AUTH_GUARD)
//             ->putJson($this->uri($this->phase), $this->body)
//             ->assertForbidden()
//             ->assertJson($this->jsonForbidden());
//     }

//     private function uri(Phase $phase): string
//     {
//         return route('v1.customers.phases.update', ['phase' => $phase->uuid]);
//     }

//     private function expectedJsonStructure(): array
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