<?php

namespace MyCerts\UI;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use MyCerts\Domain\Model\Category;

class CategoryController extends BaseController
{
    public function list()
    {
        $nonAdminRestriction = Auth::user()->isAdmin() ? [] : ['company_id' => Auth::user()->company_id];

        return response()->json(Category::where($nonAdminRestriction)->paginate(self::DEFAULT_PAGINATION_LENGHT));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name'        => 'required|string',
            'description' => 'string',
            'icon'        => 'string',
        ]);

        $company = $this->retrieveCompany($request);

        $entity = new Category(array_filter([
            'company_id'  => $company->id,
            'name'        => $request->json('name'),
            'description' => $request->json('description'),
            'icon'        => $request->json('icon'),
        ]));

        $entity->save();

        return response()->json($entity, Response::HTTP_CREATED);
    }

    public function patch(string $id, Request $request)
    {
        $this->validate($request, [
            'name'        => 'string',
            'description' => 'string',
            'icon'        => 'string',
        ]);

        $company  = $this->retrieveCompany($request);
        $category = Category::where(['id' => $id, 'company_id' => $company->id])->first();
        $category->fill(array_filter([
            'name'        => $request->json('name'),
            'description' => $request->json('description'),
            'icon'        => $request->json('icon'),
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
        $company  = $this->retrieveCompany($request);
        $category = Category::where(['id' => $id, 'company_id' => $company->id])->first();
        if ($category) {
            $category->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}