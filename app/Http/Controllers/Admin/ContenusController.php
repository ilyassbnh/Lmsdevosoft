<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyContenuRequest;
use App\Http\Requests\StoreContenuRequest;
use App\Http\Requests\UpdateContenuRequest;
use App\Models\Contenu;
use App\Models\Lecon;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class ContenusController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('contenu_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $contenus = Contenu::with(['lecon'])->get();

        return view('admin.contenus.index', compact('contenus'));
    }

    public function create()
    {
        abort_if(Gate::denies('contenu_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $lecons = Lecon::pluck('label', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.contenus.create', compact('lecons'));
    }

    public function store(StoreContenuRequest $request)
    {
        $contenu = Contenu::create($request->all());

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $contenu->id]);
        }

        return redirect()->route('admin.contenus.index');
    }

    public function edit(Contenu $contenu)
    {
        abort_if(Gate::denies('contenu_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $lecons = Lecon::pluck('label', 'id')->prepend(trans('global.pleaseSelect'), '');

        $contenu->load('lecon');

        return view('admin.contenus.edit', compact('contenu', 'lecons'));
    }

    public function update(UpdateContenuRequest $request, Contenu $contenu)
    {
        $contenu->update($request->all());

        return redirect()->route('admin.contenus.index');
    }

    public function show(Contenu $contenu)
    {
        abort_if(Gate::denies('contenu_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $contenu->load('lecon', 'contenuVideos');

        return view('admin.contenus.show', compact('contenu'));
    }

    public function destroy(Contenu $contenu)
    {
        abort_if(Gate::denies('contenu_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $contenu->delete();

        return back();
    }

    public function massDestroy(MassDestroyContenuRequest $request)
    {
        $contenus = Contenu::find(request('ids'));

        foreach ($contenus as $contenu) {
            $contenu->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('contenu_create') && Gate::denies('contenu_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new Contenu();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
