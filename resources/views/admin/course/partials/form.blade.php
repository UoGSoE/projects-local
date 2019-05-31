    <div class="field">
        <div class="control">
            <label class="label">Code</label>
            <input class="input" name="code" type="text" value="{{ old('code', $course->code) }}" required>
        </div>
    </div>

    <div class="field">
        <div class="control">
            <label class="label">Title</label>
            <input class="input" name="title" type="text" value="{{ old('title', $course->title) }}" required>
        </div>
    </div>

    <div class="field">
        <div class="control">
            <label class="label">Type</label>
            <div class="select">
                <select name="category">
                    <option value="undergrad" @if ($course->category == 'undergrad') selected @endif>Undergrad</option>
                    <option value="postgrad" @if ($course->category == 'postgrad') selected @endif>Postgrad</option>
                </select>
            </div>
        </div>
    </div>

    <div class="field">
        <div class="control">
            <label class="label">Application Deadline</label>
            <input class="input" name="application_deadline" type="text" value="{{ old('application_deadline', $course->application_deadline->format('d/m/Y')) }}" required v-pikaday>
        </div>
    </div>

    <div class="field">
        <div class="control">
            <label class="label">Allow staff to accept 1st choices of students?</label>
            <div class="select">
                <select name="allow_staff_accept">
                    <option value="1" @if (old('allow_staff_accept', $course->allow_staff_accept)) selected @endif>Yes</option>
                    <option value="0" @if (!old('allow_staff_accept', $course->allow_staff_accept)) selected @endif>No</option>
                </select>
            </div>
        </div>
    </div>