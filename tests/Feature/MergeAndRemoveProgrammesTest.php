<?php

namespace Tests\Feature;

use App\Http\Livewire\ProgrammeMerger;
use App\Models\Programme;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Symfony\Component\Console\Helper\ProgressBar;
use Tests\TestCase;

class MergeAndRemoveProgrammesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function regular_users_cant_see_the_page_to_merge_and_remove_programmes()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.programmes.merge'));

        $response->assertRedirect('/');
    }

    /** @test */
    public function admins_can_see_the_page_to_merge_and_remove_programmes()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->admin()->create();
        $programme1 = Programme::factory()->create();
        $programme2 = Programme::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.programmes.merge'));

        $response->assertSuccessful();
        $response->assertSeeLivewire('programme-merger');
        $response->assertSee($programme1->title);
        $response->assertSee($programme2->title);
    }

    /** @test */
    public function admins_can_filter_which_programmes_are_shown()
    {
        $admin = User::factory()->admin()->create();
        $programme1 = Programme::factory()->create(['category' => 'undergrad']);
        $programme2 = Programme::factory()->create(['category' => 'postgrad']);

        Livewire::actingAs($admin)->test(ProgrammeMerger::class)
            ->assertSee($programme1->title)
            ->assertSee($programme2->title)
            ->set('category', 'postgrad')
            ->assertDontSee($programme1->title)
            ->assertSee($programme2->title)
            ->set('category', 'undergrad')
            ->assertSee($programme1->title)
            ->assertDontSee($programme2->title)
            ->set('category', '')
            ->assertSee($programme1->title)
            ->assertSee($programme2->title);
    }

    /** @test */
    public function admins_can_merge_projects_and_students_from_selected_programme_into_another()
    {
        $admin = User::factory()->admin()->create();
        $programme1 = Programme::factory()->create(['category' => 'undergrad']);
        $programme2 = Programme::factory()->create(['category' => 'postgrad']);
        $programme3 = Programme::factory()->create(['category' => 'postgrad']);
        $programme4 = Programme::factory()->create(['category' => 'postgrad']);
        $projects1 = Project::factory()->count(3)->create();
        $projects2 = Project::factory()->count(3)->create();
        $projects3 = Project::factory()->count(3)->create();
        $projects4 = Project::factory()->count(3)->create();
        $programme1->projects()->sync($projects1->pluck('id'));
        $programme2->projects()->sync($projects2->pluck('id'));
        $programme3->projects()->sync($projects3->pluck('id'));
        $programme4->projects()->sync($projects3->pluck('id'));
        $students1 = User::factory()->student()->count(3)->create(['programme_id' => $programme1->id]);
        $students2 = User::factory()->student()->count(3)->create(['programme_id' => $programme2->id]);
        $students3 = User::factory()->student()->count(3)->create(['programme_id' => $programme3->id]);
        $students4 = User::factory()->student()->count(3)->create(['programme_id' => $programme4->id]);

        $this->assertCount(3, $programme1->projects);
        $this->assertCount(3, $programme2->projects);
        $this->assertCount(3, $programme3->projects);
        $this->assertCount(3, $programme4->projects);
        $this->assertCount(3, $programme1->students);
        $this->assertCount(3, $programme2->students);
        $this->assertCount(3, $programme3->students);
        $this->assertCount(3, $programme4->students);

        Livewire::actingAs($admin)->test(ProgrammeMerger::class)
            ->set('mergeFrom', [$programme1->id, $programme2->id])
            ->set('mergeTo', $programme3->id)
            ->call('merge')
            ->assertSet('mergeFrom', [])
            ->assertSet('mergeTo', null);

        tap($programme1->fresh(), function ($programme1) {
            $this->assertCount(0, $programme1->projects);
            $this->assertCount(0, $programme1->students);
        });
        tap($programme2->fresh(), function ($programme2) {
            $this->assertCount(0, $programme2->projects);
            $this->assertCount(0, $programme2->students);
        });
        tap($programme3->fresh(), function ($programme3) use ($projects1, $projects2, $projects3) {
            $this->assertCount(9, $programme3->projects);
            $this->assertCount(9, $programme3->students);
            $projects1->each(fn ($project) => $this->assertTrue($programme3->projects->contains($project)));
            $projects2->each(fn ($project) => $this->assertTrue($programme3->projects->contains($project)));
            $projects3->each(fn ($project) => $this->assertTrue($programme3->projects->contains($project)));
        });
        tap($programme4->fresh(), function ($programme4) {
            $this->assertCount(3, $programme4->projects);
            $this->assertCount(3, $programme4->students);
        });
    }

    /** @test */
    public function admins_can_remove_programmes_with_zero_projects_and_zero_students_attached()
    {
        $admin = User::factory()->admin()->create();
        $programme1 = Programme::factory()->create(['category' => 'undergrad']);
        $programme2 = Programme::factory()->create(['category' => 'postgrad']);
        $programme3 = Programme::factory()->create(['category' => 'postgrad']);
        $projects1 = Project::factory()->count(3)->create();
        $programme1->projects()->sync($projects1->pluck('id'));
        $students2 = User::factory()->student()->count(3)->create(['programme_id' => $programme2->id]);

        Livewire::actingAs($admin)->test(ProgrammeMerger::class)
            ->assertSee($programme1->title)
            ->assertSee($programme2->title)
            ->assertSee($programme3->title)
            ->call('remove', $programme1->id)
            ->assertSee($programme1->title)
            ->assertSee($programme2->title)
            ->assertSee($programme3->title)
            ->call('remove', $programme2->id)
            ->assertSee($programme1->title)
            ->assertSee($programme2->title)
            ->assertSee($programme3->title)
            ->call('remove', $programme3->id)
            ->assertSee($programme1->title)
            ->assertSee($programme2->title)
            ->assertDontSee($programme3->title);

        $this->assertDatabaseHas('programmes', ['id' => $programme1->id]);
        $this->assertDatabaseHas('programmes', ['id' => $programme2->id]);
        $this->assertDatabaseMissing('programmes', ['id' => $programme3->id]);
    }

    /** @test */
    public function admin_can_toggle_the_list_of_projects_for_a_programme()
    {
        $admin = User::factory()->admin()->create();
        $programme1 = Programme::factory()->create(['category' => 'undergrad']);
        $programme2 = Programme::factory()->create(['category' => 'postgrad']);
        $projects1 = Project::factory()->count(3)->create();
        $projects2 = Project::factory()->count(3)->create();
        $programme1->projects()->sync($projects1->pluck('id'));
        $programme2->projects()->sync($projects2->pluck('id'));

        Livewire::actingAs($admin)->test(ProgrammeMerger::class)
            ->assertSee($programme1->title)
            ->assertSee($programme2->title)
            ->assertDontSee($projects1->first()->title)
            ->call('toggleProjectListing', $programme1->id)
            ->assertSee($programme1->title)
            ->assertSee($programme2->title)
            ->assertSee($projects1->first()->title)
            ->call('toggleProjectListing', $programme1->id)
            ->assertSee($programme1->title)
            ->assertSee($programme2->title)
            ->assertDontSee($projects1->first()->title);
    }

    /** @test */
    public function admin_can_toggle_the_list_of_students_for_a_programme()
    {
        $admin = User::factory()->admin()->create();
        $programme1 = Programme::factory()->create(['category' => 'undergrad']);
        $programme2 = Programme::factory()->create(['category' => 'postgrad']);
        $students1 = User::factory()->student()->count(3)->create(['programme_id' => $programme1->id]);
        $students2 = User::factory()->student()->count(3)->create(['programme_id' => $programme2->id]);

        Livewire::actingAs($admin)->test(ProgrammeMerger::class)
            ->assertSee($programme1->title)
            ->assertSee($programme2->title)
            ->assertDontSee($students1->first()->full_name)
            ->call('toggleStudentListing', $programme1->id)
            ->assertSee($programme1->title)
            ->assertSee($programme2->title)
            ->assertSee($students1->first()->full_name)
            ->call('toggleStudentListing', $programme1->id)
            ->assertSee($programme1->title)
            ->assertSee($programme2->title)
            ->assertDontSee($students1->first()->title);
    }
}
