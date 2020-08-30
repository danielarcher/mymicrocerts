<?php

namespace MyCertsTests\System;

use MyCertsTests\TestCase;

class CategoryTest extends TestCase
{
    public function test_it_should_create_category()
    {
        $createdCategory = $this->faker->paragraph;

        $this->json('POST', '/api/category', [
            "name" => $createdCategory
        ], ['Authorization' => $this->companyToken()]);

        $this->assertResponseCreated();
        $this->seeInDatabase('category', ['name' => $createdCategory, 'deleted_at' => null]);
    }

    public function test_it_should_delete_category()
    {
        $createdCategory = $this->faker->paragraph;

        /**
         * create
         */
        $this->json('POST', '/api/category', [
            "name" => $createdCategory
        ], ['Authorization' => $this->companyToken()]);

        $category = $this->response->getOriginalContent();

        /**
         * delete
         */
        $this->json('DELETE', '/api/category/' . $category->id, [], ['Authorization' => $this->companyToken()]);

        $this->assertResponseNoContent();
        $this->notSeeInDatabase('category', ['name' => $createdCategory, 'deleted_at' => null]);
    }

    public function test_it_should_update_category()
    {
        $createdCategory = $this->faker->paragraph;

        /**
         * create
         */
        $this->json('POST', '/api/category', [
            "name" => $createdCategory
        ], ['Authorization' => $this->companyToken()]);

        $category = $this->response->getOriginalContent();

        /**
         * update
         */
        $updatedName = $this->faker->paragraph;
        $this->json('PATCH', '/api/category/' . $category->id, [
            "name" => $updatedName
        ], ['Authorization' => $this->companyToken()]);

        $this->assertResponseOk();
        $this->seeInDatabase('category', ['id' => $category->id, 'name' => $updatedName, 'deleted_at' => null]);
    }

}