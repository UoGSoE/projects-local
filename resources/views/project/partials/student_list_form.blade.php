<form method="POST" action="{{ route('project.accept_students', $project->id) }}">
    {{ csrf_field() }}
    <span class="display: flex;">
    @foreach (App\User::all() as $student)
        <li style="display:flex;margin-bottom: 1em;">
            <label style="flex-shrink: 1; margin-right: 1em;">
                @can('accept-onto-project', [$student, $project])
                    <input type="checkbox" name="students[{{ $student->id }}]" value="1">
                @endcan
                {{ $student->full_name }}
            </label>
            @if ($student->profile)
            <span role="button" @click='selectedStudent = @json($student)' style="cursor: pointer;flex-grow: 1;" title="Show students profile">
                <span class="icon" style="width: 1em;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5 5a5 5 0 0 1 10 0v2A5 5 0 0 1 5 7V5zM0 16.68A19.9 19.9 0 0 1 10 14c3.64 0 7.06.97 10 2.68V20H0v-3.32z"/></svg>
                </span>
            </span>
            @endif
        </li>
    @endforeach
    </span>
    @if ($project->students->count() > 0)
        <button type="submit" name="accept">Accept Students</button>
    @endif
</form>