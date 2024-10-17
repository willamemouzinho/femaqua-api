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
// use App\Models\Project;
// use App\Models\CustomerEmployee;
// use Database\Seeders\ActionSeed;
// use App\Http\Resources\V1\PhaseResource;
// use Illuminate\Database\Eloquent\Collection;

// class GetPhasesTest extends TestCase
// {
//     private CustomerEmployee $customer_employee;
//     private Project $project;
//     private Collection $phases;

//     function setUp(): void
//     {
//         parent::setUp();

//         $this->seed([ActionSeed::class]);

//         $this->customer_employee = CustomerEmployee::factory()->withActions(['view-project'])->create();
//         $this->project = Project::factory()->create(
//             ['customer_uuid' => $this->customer_employee->customer_uuid]
//         );
//         $this->phases = Phase::factory(3)->for($this->project)->create();
//     }

//     public function testNonAdminUserCannotGetThePhases(): void
//     {
//         $non_admin_user = User::factory()->create();

//         $this->actingAs($non_admin_user, self::AUTH_GUARD)
//             ->getJson($this->uri($this->project))
//             ->assertForbidden()
//             ->assertJson([]);
//     }

//     public function testAdminUsersCanGetThePhases(): void
//     {
//         $admin_user = User::factory()->admin()->create();

//         $this->actingAs($admin_user, self::AUTH_GUARD)
//             ->getJson($this->uri($this->project))
//             ->assertOk()
//             ->assertJson([]);
//     }

//     public function testCustomerEmployeeCanGetThePhases(): void
//     {
//         $this->actingAs($this->customer_employee->user, self::AUTH_GUARD)
//             ->getJson($this->uri($this->project))
//             ->assertOk()
//             ->assertJsonCount(3, 'data')
//             ->assertJsonStructure($this->expectedJsonStructure())
//             ->assertJson($this->expectedJson($this->phases));
//     }

//     public function testGetThePhasesFilteredByName(): void
//     {
//         $phases = Phase::factory()->for($this->project)->createMany([
//             ['name' => 'project test 1'],
//             ['name' => 'project test 2'],
//         ]);

//         $this->actingAs($this->customer_employee->user, self::AUTH_GUARD)
//             ->getJson($this->uri($this->project, 'project test'))
//             ->assertOk()
//             ->assertJsonCount(2, 'data')
//             ->assertJsonStructure($this->expectedJsonStructure())
//             ->assertJson($this->expectedJson($phases));
//     }

//     public function testPaginationReturnsOnly15Phases(): void
//     {
//         Phase::factory(20)->for($this->project)->create();

//         $this->actingAs($this->customer_employee->user, self::AUTH_GUARD)
//             ->getJson($this->uri($this->project))
//             ->assertOk()
//             ->assertJsonCount(15, 'data')
//             ->assertJsonStructure($this->expectedJsonStructure())
//             ->assertJson($this->expectedJson($this->phases));
//     }

//     private function expectedJson(Collection $phases): array
//     {
//         return ['data' => PhaseResource::collection($phases)->resolve()];
//     }

//     private function uri(Project $project, string $name = null): string
//     {
//         return route('v1.customers.phases.index', [
//             'project' => $project->uuid,
//             'name' => $name,
//         ]);
//     }

//     private function expectedJsonStructure(): array
//     {
//         return [
//             'data' => [
//                 '*' => [
//                     'uuid',
//                     'project_uuid',
//                     'name',
//                     'code',
//                     'start_at',
//                     'end_at',
//                     'budget',
//                     'alert',
//                 ]
//             ]
//         ];
//     }
// }