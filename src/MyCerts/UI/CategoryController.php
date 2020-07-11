<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use MyCerts\Domain\Model\Category;
use MyCerts\Domain\Roles;

class CategoryController extends Controller
{
    public function list()
    {
        $nonAdminRestriction = Auth::user()->isAdmin() ? [] : ['company_id'=>Auth::user()->company_id];

        return response()->json(Category::where($nonAdminRestriction)->paginate(self::DEFAULT_PAGINATION_LENGHT));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name'         => 'required',
        ]);

        $company_id = Auth::user()->isAdmin()
            ? $request->get('company_id', Auth::user()->company_id)
            : Auth::user()->company_id;

        $entity = new Category(array_filter([
            'company_id' => $company_id,
            'name'       => $request->get('name'),
        ]));

        $entity->save();

        return response()->json($entity, Response::HTTP_CREATED);
    }

    public function findOne($id)
    {
        return response()->json(Category::find($id));
    }

    public function delete($id)
    {
        if (!Category::find($id)) {
            return response()->json(['error' => 'Entity not found'], Response::HTTP_NOT_FOUND);
        }
        Category::destroy($id);
        return response(null,Response::HTTP_NO_CONTENT);
    }
}