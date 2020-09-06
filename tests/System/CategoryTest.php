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

    public function test_it_should_create_category_with_custom_attributes()
    {
        $createdCategory = $this->faker->paragraph;

        $this->json('POST', '/api/category', [
            "name" => $createdCategory,
            "custom" => [
                'myattr' => 123
            ],
        ], ['Authorization' => $this->companyToken()]);

        $this->assertResponseCreated();
        $this->seeInDatabase('category', ['name' => $createdCategory,'custom->myattr'=>123, 'deleted_at' => null]);
    }

    public function test_it_should_update_custom_attribute()
    {
        $createdCategory = $this->faker->paragraph;

        $this->json('POST', '/api/category', [
            "name" => $createdCategory,
            "custom" => [
                'myattr' => 123
            ],
        ], ['Authorization' => $this->companyToken()]);
        $category = $this->response->getOriginalContent();

        $this->json('PATCH', '/api/category/'.$category->id, [
            "custom" => [
                'color' => 'red'
            ],
        ], ['Authorization' => $this->companyToken()]);

        $this->assertResponseOk();
        $this->notSeeInDatabase('category', ['name' => $createdCategory,'custom->myattr'=>123, 'deleted_at' => null]);
        $this->seeInDatabase('category', ['name' => $createdCategory,'custom->color'=>'red', 'deleted_at' => null]);
    }

    public function test_it_should_create_category_with_description_and_icon()
    {
        $createdCategory = $this->faker->paragraph;
        $description     = $this->faker->paragraph;

        $this->json('POST', '/api/category', [
            "name"        => $createdCategory,
            "description" => $description,
            "icon"        => $this->faker->paragraph,
        ], ['Authorization' => $this->companyToken()]);

        $this->assertResponseCreated();
        $this->seeInDatabase('category', ['description' => $description, 'deleted_at' => null]);
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
            "name" => $createdCategory,
            "description" => $createdCategory,
            "icon" => $createdCategory,
        ], ['Authorization' => $this->companyToken()]);

        $category = $this->response->getOriginalContent();

        /**
         * update
         */
        $updatedName = $this->faker->paragraph;
        $updatedDescription = $this->faker->paragraph;
        $this->json('PATCH', '/api/category/' . $category->id, [
            "name" => $updatedName,
            "description" => $updatedDescription,
        ], ['Authorization' => $this->companyToken()]);

        $this->assertResponseOk();
        $this->seeInDatabase('category', ['id' => $category->id, 'name' => $updatedName, 'description' => $updatedDescription, 'deleted_at' => null]);
    }

}