    <div class="field">
        <div class="control">
            <label class="label">Title</label>
            <input class="input" name="title" type="text" value="{{ old('title', $project->title) }}" autofocus required>
        </div>
    </div>

    @if ($project->category == 'undergrad')
        <div class="field">
            <div class="control">
                <label class="label">Type</label>
                <label class="radio">
                    <input type="radio" name="type" value="B.Eng" required>
                    B.Eng
                </label>
                <label class="radio">
                    <input type="radio" name="type" value="M.Eng">
                    M.Eng
                </label>
                <label class="radio">
                    <input type="radio" name="type" value="SIT/UESTC">
                    SIT/UESTC
                </label>
            </div>
        </div>
    @endif

    <div class="field">
        <div class="control">
            <label class="label">Description</label>
            <textarea class="textarea" name="description" required>{{ old('description', $project->description) }}</textarea>
        </div>
    </div>

    <div class="field">
        <div class="control">
            <label class="label">Pre-requisit skills</label>
            <textarea class="textarea" name="pre_req">{{ old('pre_req', $project->pre_req) }}</textarea>
        </div>
    </div>

    <div class="columns">

        <div class="column">
            <div class="field">
                <div class="control">
                    <label class="label">Applicable Degree Programmes</label>
                    <ul>
                    @foreach ($programmes as $programme)
                        <li>
                            <div class="field">
                                <div class="control">
                                    <label>
                                        <input type="checkbox" class="checkbox" name="programmes[]" value="{{ $programme->id}}"
                                            @if (in_array($programme->id, old('programmes', $project->programmes->pluck('id')->toArray()))) checked @endif
                                        >
                                        {{ $programme->title }}
                                    </label>
                                </div>
                            </div>
                        </li>
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="column">
            <div class="field">
                <div class="control">
                    <label class="label">Applicable Courses</label>
                    <ul>
                    @foreach ($courses as $course)
                        <li>
                            <div class="field">
                                <div class="control">
                                    <label>
                                        <input type="checkbox" class="checkbox" name="courses[]" value="{{ $course->id}}"
                                            @if (in_array($course->id, old('courses', $project->courses->pluck('id')->toArray()))) checked @endif
                                        >
                                        {{ $course->code }} {{ $course->title }}
                                    </label>
                                </div>
                            </div>
                        </li>
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->isAdmin())
        <div class="field">
            <div class="control">
                <label class="label">
                    Project Owner
                </label>
                <div class="select">
                    <select name="staff_id">
                        @foreach ($staff as $user)
                            <option value="{{ $user->id }}" @if ($user->id == old('staff_id', $project->staff_id)) selected @endif>
                                {{ $user->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endif

    <div class="field">
        <div class="control">
            <label class="label">Maximum Number of Students</label>
            <input class="input" name="max_students" type="number" value="{{ old('max_students', $project->max_students) }}" min="0" step="1" required>
        </div>
    </div>

    <div class="field">
        <div class="control">
            <label>
                <input type="hidden" name="is_active" value="0">
                <input class="checkbox" name="is_active" type="checkbox" value="1" @if (old('is_active', $project->is_active)) checked @endif>
                Active?
            </label>
        </div>
    </div>

    <div class="field">
        <div class="control">
            <label>
                <input type="hidden" name="is_placement" value="0">
                <input class="checkbox" name="is_placement" type="checkbox" value="1" @if (old('is_placement', $project->is_placement)) checked @endif>
                A placement?
            </label>
        </div>
    </div>
    <div class="field">
        <div class="control">
            <label>
                <input type="hidden" name="is_confidential" value="0">
                <input class="checkbox" name="is_confidential" type="checkbox" value="1" @if (old('is_confidential', $project->is_confidential)) checked @endif>
                Confidential?
            </label>
        </div>
    </div>
