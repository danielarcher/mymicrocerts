<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use MyCerts\Domain\Model\Category;
use MyCerts\Domain\Roles;

class CategoryController extends BaseController
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

        $company = $this->retrieveCompany($request);

        $entity = new Category(array_filter([
            'company_id' => $company->id,
            'name'       => $request->get('name'),
        ]));

        $entity->save();

        return response()->json($entity, Response::HTTP_CREATED);
    }

    public function patch(string $id, Request $request)
    {
        $this->validate($request, [
            'name'         => 'required',
        ]);

        $company = $this->retrieveCompany($request);
        $category = Category::where(['id' => $id, 'company_id' => $company->id])->first();
        $category->fill(array_filter([
            'name' => $request->json('name'),
        ]));
        $category->save();

        return response()->json($category);
    }

    public function findOne($id)
    {
        return response()->json(Category::find($id));
    }

    public function delete($id, Request $request)
    {
        $company = $this->retrieveCompany($request);
        $category = Category::where(['id' => $id, 'company_id' => $company->id])->first();
        $category->delete();

        return response(null,Response::HTTP_NO_CONTENT);
    }
}