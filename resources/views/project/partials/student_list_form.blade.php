<form method="POST" action="{{ route('project.accept_students', $project->id) }}">
    {{ csrf_field() }}
    @foreach ($project->students as $student)
        <li>
            <label>
                @can('accept-onto-project', [$student, $project])
                    <input type="checkbox" name="students[{{ $student->id }}]" value="1">
                @endcan
                {{ $student->full_name }}
            </label>
        </li>
    @endforeach
    <button type="submit" name="accept">Accept Students</button>
</form>