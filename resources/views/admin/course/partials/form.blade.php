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
