<?php

use Illuminate\Foundation\Application;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Photo;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Route::middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified',
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return Inertia::render('Dashboard');
//     })->name('dashboard');
// });
Route::middleware(['auth:sanctum',config('jetstream.auth_session'), 'verified'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // other admin routes here
    Route::get('/photos', function () {
        return inertia('Admin/Photos', [
            'photos' => Photo::all()
        ]);
    })->name('photos'); // This will respond to requests for admin/photos and have a name of admin.photos
    //新增页
    Route::get('/photos/create', function () {
        return inertia('Admin/PhotosCreate');
    })->name('photos.create');
    //新增
    Route::post('/photos', function (Request $request) {
        //dd('I will handle the form submission')

        //dd(Request::all());
        $validated_data = $request->validate([
            'path' => ['required', 'image', 'max:2500'],
            'description' => ['required']
        ]);
//        dd($validated_data);
//        $path = Storage::disk('public')->put('photos', $request->file('path'));
        $path = Storage::disk('public')->putFile('photos', $request->file('path'));
//        dd($path);
        $validated_data['path'] = Storage::url($path);
//        dd($validated_data);
//        $validated_data1 = ['path'=>'test6','description'=>'description6'];
//        $validated_data2 = ['path'=>'test7','description'=>'description7'];
//        $validated_data3 = ['path'=>'test8','description'=>'description8'];
//        Photo::upsert([$validated_data1,$validated_data2,$validated_data3],['path']);

        Photo::create($validated_data);
        return to_route('admin.photos');
    })->name('photos.store');

    //编辑页
    Route::get('/photos/{photo}/edit', function(Photo $photo){
        return inertia('Admin/PhotosEdit', [
            'photo' => $photo
        ]);
    })->name('photos.edit');

    //更新
    Route::put('/photos/{photo}', function (Request $request, Photo $photo)
    {
        //dd(Request::all());

        $validated_data = $request->validate([
            'description' => ['required']
        ]);

        if ($request->hasFile('path')) {
            $validated_data['path'] = $request->validate([
                'path' => ['required', 'image', 'max:1500'],

            ]);

            // Grab the old image and delete it
            // dd($validated_data, $photo->path);
            $oldImage = $photo->path;
            Storage::delete($oldImage);

            $path = Storage::disk('public')->putFile('photos', $request->file('path'));
            $validated_data['path'] = $path;
        }

        //dd($validated_data);

        $photo->update($validated_data);
        return to_route('admin.photos');
    })->name('photos.update');

    //删除
    Route::delete('/photos/{photo}', function (Photo $photo)
    {
        Storage::delete($photo->path);
        $photo->delete();
        return to_route('admin.photos');
    })->name('photos.delete');
});

Route::get('photos', function () {
    //dd(Photo::all());
    return Inertia::render('Guest/Photos', [
        'photos' => Photo::all(), ## 👈 Pass a collection of photos, the key will become our prop in the component
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});
