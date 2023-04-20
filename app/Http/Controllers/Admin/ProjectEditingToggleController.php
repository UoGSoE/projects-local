<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectEditingToggleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => 'required|in:undergrad,postgrad',
        ]);

        $optionName = "{$data['category']}_editing_disabled";

        if (option($optionName)) {
            option()->remove($optionName);
        } else {
            option([$optionName => now()->format('Y-m-d H:i')]);
        }

        return redirect(route('admin.project.index', ['category' => $data['category']]));
    }
}
