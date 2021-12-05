<div>
    <div class="level">
        <div class="level-left">
            <div class="field level-item">
                <label class="label">Show</label>
                <div class="control">
                    <div class="select">
                    <select wire:model="category" name="category">
                        <option value="">All</option>
                        <option value="undergrad">Undergrad</option>
                        <option value="postgrad">Postgrad</option>
                    </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <button wire:click="merge" class="button is-info" @if ((! $mergeTo) or (count($mergeFrom) == 0)) disabled @endif>Merge</button>
            </div>
        </div>
    </div>
    <hr>
    @error('mergeFrom')
        <div class="notification is-danger">
            {{ $message }}
        </div>
    @enderror
    @error('mergeTo')
        <div class="notification is-danger">
            {{ $message }}
        </div>
    @enderror
    <div class="columns">
        <div class="column">
            <h4 class="title is-4">Merge From</h4>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Merge?</th>
                        <th>Programme</th>
                        <th>Projects</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($programmes as $programme)
                        <tr>
                            <td>
                                <input type="checkbox" name="programme_id[]" value="{{ $programme->id }}" wire:model="mergeFrom">
                            </td>
                            <td>
                                {{ $programme->title }}
                            </td>
                            <td>
                                <div class="buttons has-addons">
                                    @if ($programme->projects->count() === 0 && $programme->students->count() === 0)
                                        <button class="button is-outlined is-danger" wire:click="remove({{ $programme->id }})">Remove Programme</button>
                                    @else
                                        @if (in_array($programme->id, $showProjectLists))
                                            <button class="button" wire:click="toggleProjectListing({{ $programme->id }})">Hide Projects</button>
                                            <ul>
                                            @foreach ($programme->projects as $project)
                                                <li style="margin-bottom: 0.2rem;">&middot; <a href="{{ route('project.show', $project->id) }}">{{ $project->title }}</a></li>
                                            @endforeach
                                            </ul>
                                        @else
                                            <button class="button" wire:click="toggleProjectListing({{ $programme->id }})">{{ $programme->projects->count() }} Projects</button>
                                        @endif
                                        @if (in_array($programme->id, $showStudentLists))
                                            <button class="button" wire:click="toggleStudentListing({{ $programme->id }})">Hide Students</button>
                                            <ul>
                                            @foreach ($programme->students as $student)
                                                <li style="margin-bottom: 0.2rem;">&middot; {{ $student->full_name }} ({{ $student->username }})</li>
                                            @endforeach
                                            </ul>
                                        @else
                                            <button class="button" wire:click="toggleStudentListing({{ $programme->id }})">{{ $programme->students->count() }} Students</button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="column">
            <h4 class="title is-4">Merge Into</h4>
            <ul>
                @foreach ($programmes as $programme)
                    <li style="margin-bottom: 0.4rem;">
                        <div class="field">
                            <div class="control">
                              <label class="radio">
                                <input type="radio" name="mergeTo" wire:model="mergeTo" value="{{ $programme->id }}">
                                {{ $programme->title }} ({{ $programme->projects->count() }} projects / {{ $programme->students->count() }} students)
                              </label>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
