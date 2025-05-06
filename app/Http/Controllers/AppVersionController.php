<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppVersionController extends Controller
{
    // Allow the user to download the app if they have a business account.
    public function download()
    {
        return redirect(config('app.current_version_download_url'));
/*        if ($userCanDownload) {
            return Storage::response(
                env('BUSINESS_APP_DOWNLOAD_URL'),
                'SB-Business',
                [
                    'Content-Type' => 'application/vnd.android.package-archive',
                    'Content-Disposition' => 'attachment; filename="SB-Business.apk"',
                ],
                'attachment'
            );
        }

        return response()->noContent();*/
    }

    /**
     * Check if the user is on the correct version of the app.
     */
    public function check(Request $request)
    {
        $validatedData = $request->validate([
            'installedVersion' => 'required|string'
        ]);

        $user = Auth::user();
        $v = AppVersion::where('user_id', $user->id)->get();

        $currentVersion = '';
        if ( count($v) > 0 && $validatedData['installedVersion'] !== $v[0]->version) {
            $v[0]->version = $validatedData['installedVersion'];
            $v[0]->save();
            $v[0]->refresh();
            $currentVersion = $v[0]->version;
        } else {
            if ( count($v) === 0 ) {
                $k = new AppVersion();
                $k->user_id = $user->id;
                $k->version = $validatedData['installedVersion'];
                $k->save();
                $k->refresh();
                $currentVersion = $k->version;
            } else {
                $v[0]->save();
                $currentVersion = $v[0]->version;
            }
        }

        $needsUpdate = false;

        if ($currentVersion !== config('app.front_end_business_version') ) {
            $needsUpdate = true;
        }

        return response([
            'currentVersion' => $currentVersion,
            'needsUpdate' => $needsUpdate
        ]);
    }
}
