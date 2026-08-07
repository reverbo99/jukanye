<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Throwable;

class ArtisanCommandController extends Controller
{
    public function index(): View
    {
        return view('admin.artisan.index');
    }

    public function migrate(): RedirectResponse
    {
        return $this->run('migrate', [], 'php artisan migrate');
    }

    public function migrateForce(): RedirectResponse
    {
        return $this->run('migrate', ['--force' => true], 'php artisan migrate --force');
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function run(string $command, array $parameters, string $label): RedirectResponse
    {
        try {
            $exitCode = Artisan::call($command, $parameters);
            $output = trim(Artisan::output());

            return redirect()
                ->route('admin.artisan.index')
                ->with($exitCode === 0 ? 'success' : 'error', $label.' finished with exit code '.$exitCode.'.')
                ->with('artisan_output', $output !== '' ? $output : '(no output)');
        } catch (Throwable $e) {
            return redirect()
                ->route('admin.artisan.index')
                ->with('error', $label.' failed: '.$e->getMessage())
                ->with('artisan_output', $e->getMessage());
        }
    }
}
