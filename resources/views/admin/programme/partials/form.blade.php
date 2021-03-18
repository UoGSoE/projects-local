    <div class="field">
        <div class="control">
            <label class="label">Title</label>
            <input class="input" name="title" type="text" value="{{ old('title', $programme->title) }}" required>
        </div>
    </div>

    <div class="field">
        <div class="control">
            <label class="label">Plan Code</label>
            <input class="input" name="plan_code" type="text" value="{{ old('plan_code', $programme->plan_code) }}">
        </div>
    </div>

    <div class="field">
        <div class="control">
            <label class="label">Type</label>
            <div class="select">
                <select name="category">
                    <option value="undergrad" @if ($programme->category == 'undergrad') selected @endif>Undergrad</option>
                    <option value="postgrad" @if ($programme->category == 'postgrad') selected @endif>Postgrad</option>
                </select>
            </div>
        </div>
    </div>
