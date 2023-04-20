<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature\Admin\Export;

use App\Exports\ProgrammesExport;
use App\Models\Programme;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ProgrammeExportTest extends TestCase
{
    /** @test */
    public function an_admin_can_download_a_csv_of_all_programmes(): void
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $programme1 = create(Programme::class, ['title' => 'Abc']);
        $programme2 = create(Programme::class, ['title' => 'Xyz']);

        $response = $this->actingAs($admin)->get(route('export.programmes', ['format' => 'csv']));

        $response->assertOk();
        Excel::assertDownloaded('uog_programmes.csv', function (ProgrammesExport $export) use ($programme1, $programme2) {
            //3 rows, 2 programmes + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($programme1->title, $export->collection()[1]['title']);
            $this->assertEquals($programme1->category, $export->collection()[1]['category']);
            $this->assertEquals($programme1->projects->count(), $export->collection()[1]['projects_count']);
            $this->assertEquals($programme1->places_count, $export->collection()[1]['places_count']);
            $this->assertEquals($programme1->accepted_count, $export->collection()[1]['accepted_count']);

            $this->assertEquals($programme2->title, $export->collection()[2]['title']);
            $this->assertEquals($programme2->category, $export->collection()[2]['category']);
            $this->assertEquals($programme2->projects->count(), $export->collection()[2]['projects_count']);
            $this->assertEquals($programme2->places_count, $export->collection()[2]['places_count']);
            $this->assertEquals($programme2->accepted_count, $export->collection()[2]['accepted_count']);

            return true;
        });
    }

    /** @test */
    public function an_admin_can_download_an_xlsx_of_all_programmes(): void
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $programme1 = create(Programme::class, ['title' => 'Abc']);
        $programme2 = create(Programme::class, ['title' => 'Xyz']);

        $response = $this->actingAs($admin)->get(route('export.programmes', ['format' => 'xlsx']));

        $response->assertOk();
        Excel::assertDownloaded('uog_programmes.xlsx', function (ProgrammesExport $export) use ($programme1, $programme2) {
            //3 rows, 2 programmes + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($programme1->title, $export->collection()[1]['title']);
            $this->assertEquals($programme1->category, $export->collection()[1]['category']);
            $this->assertEquals($programme1->projects->count(), $export->collection()[1]['projects_count']);
            $this->assertEquals($programme1->places_count, $export->collection()[1]['places_count']);
            $this->assertEquals($programme1->accepted_count, $export->collection()[1]['accepted_count']);

            $this->assertEquals($programme2->title, $export->collection()[2]['title']);
            $this->assertEquals($programme2->category, $export->collection()[2]['category']);
            $this->assertEquals($programme2->projects->count(), $export->collection()[2]['projects_count']);
            $this->assertEquals($programme2->places_count, $export->collection()[2]['places_count']);
            $this->assertEquals($programme2->accepted_count, $export->collection()[2]['accepted_count']);

            return true;
        });
    }
}
