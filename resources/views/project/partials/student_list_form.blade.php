<form method="POST" action="{{ route('project.accept_students', $project->id) }}">
    {{ csrf_field() }}
    <table class="table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Choice</th>
                <th>Accepted?</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($project->students as $student)
                <tr>
                    <td>
                        @if (Auth::user()->isAdmin())
                            <a href="{{ route('admin.user.show', $student->id) }}">
                                {{ $student->full_name }}
                            </a>
                        @else
                            {{ $student->full_name }}
                        @endif
                        @if ($student->profile)
                        <span role="button" @click='selectedStudent = @json($student)' style="cursor: pointer;flex-grow: 1;" title="Show students profile">
                            <span class="icon" style="width: 1em;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5 5a5 5 0 0 1 10 0v2A5 5 0 0 1 5 7V5zM0 16.68A19.9 19.9 0 0 1 10 14c3.64 0 7.06.97 10 2.68V20H0v-3.32z"/></svg>
                            </span>
                        </span>
                        @endif
                    </td>
                    <td>
                        {{ $student->pivot->choice }}
                    </td>
                    <td>
                        <label>
                            @can('accept-onto-project', [$student, $project])
                                <input type="checkbox" name="students[{{ $student->id }}]" value="{{ $student->id }}" @if ($student->pivot->is_accepted) checked @endif>
                            @else
                                {{ $student->pivot->is_accepted ? 'Yes' : 'No' }}
                            @endcan
                        </label>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($project->students->count() > 0)
        <button class="button" type="submit" name="accept">Accept Students</button>
    @endif
</form>