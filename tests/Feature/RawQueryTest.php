<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use function Laravel\Prompts\select;

class RawQueryTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        DB::delete('DELETE FROM categories');
    }

    public function testCrud()
    {
        DB::insert('INSERT INTO categories(id, name, description, created_at) values (?, ?, ?, ?)',[
            "GADGET","Gadget","Gadget Category","2023-01-01 10:10:10"
        ]);

        $results = DB::select('SELECT * FROM categories WHERE id = ?', ['GADGET']);

        self::assertCount(1, $results);
        self::assertEquals('GADGET', $results[0]->id);
        self::assertEquals('Gadget', $results[0]->name);
        self::assertEquals('Gadget Category', $results[0]->description);
        self::assertEquals('2023-01-01 10:10:10', $results[0]->created_at);
    }

    public function testCrudNamedParameter()
    {
        DB::insert('INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)',[
            "id" => "GADGET",
            "name" => "Gadget",
            "description" => "Gadget Category",
            "created_at" => "2023-01-01 10:10:10"
        ]);

        $results = DB::select('SELECT * FROM categories WHERE id = ?', ['GADGET']);

        self::assertCount(1, $results);
        self::assertEquals('GADGET', $results[0]->id);
        self::assertEquals('Gadget', $results[0]->name);
        self::assertEquals('Gadget Category', $results[0]->description);
        self::assertEquals('2023-01-01 10:10:10', $results[0]->created_at);
    }
}
