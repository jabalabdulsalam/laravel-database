<?php

namespace Tests\Feature;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        DB::delete("DELETE FROM products");
        DB::delete("DELETE FROM categories");
    }

    public function testInsert()
    {
        DB::table("categories")->insert([
            "id" => "GADGET",
            "name" => "Gadget",
        ]);
        DB::table("categories")->insert([
            "id" => "FOOD",
            "name" => "Food",
        ]);

        $result = DB::select("SELECT COUNT(id) as total FROM categories");
        self::assertEquals(2, $result[0]->total);
    }

    public function testSelect()
    {
        $this->testInsert();

        $collection = DB::table("categories")->select(["id", "name"])->get();
        self::assertNotNull($collection);

        $collection->each(function ($item){
           Log::info(json_encode($item));
        });
    }

    public function insertCategories()
    {
        DB::table("categories")
            ->insert(['id'=>'SMARTPHONE', 'name'=>'Smartphone', 'created_at' => '2022-03-08 20:20:20']);
        DB::table("categories")
            ->insert(['id'=>'FOOD', 'name'=>'Food', 'created_at' => '2022-03-08 20:20:20']);
        DB::table("categories")
            ->insert(['id'=>'LAPTOP', 'name'=>'Laptop', 'created_at' => '2022-03-08 20:20:20']);
        DB::table("categories")
            ->insert(['id'=>'FASHION', 'name'=>'Fashion', 'created_at' => '2022-03-08 20:20:20']);
    }

    public function testWhere()
    {
        $this->insertCategories();

        $collection = DB::table("categories")->where(function (Builder $builder){
           $builder->where('id', '=', 'SMARTPHONE');
           $builder->orWhere('id', '=', 'LAPTOP');
           //SELECT * FROM categories WHERE (id = SMARTPHONE OR id = LAPTOP)
        })->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item){
            Log::info(json_encode($item));
        });
    }

    public function testWhereBetween()
    {
        $this->insertCategories();

        $collection = DB::table("categories")
            ->whereBetween("created_at", ["2022-02-08 20:20:20", "2022-04-08 20:20:20"])
            ->get();
        self::assertCount(4, $collection);
        $collection->each(function ($item){
            Log::info(json_encode($item));
        });
    }

    public function testWhereIn()
    {
        $this->insertCategories();

        $collection = DB::table("categories")->whereIn("id", ["SMARTPHONE", "LAPTOP"])->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item){
            Log::info(json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->insertCategories();

        $collection = DB::table("categories")
            ->whereNull("description")->get();

        self::assertCount(4, $collection);
        $collection->each(function ($item){
            Log::info(json_encode($item));
        });
    }

    public function testWhereDate()
    {
        $this->insertCategories();

        $collection = DB::table("categories")
            ->whereDate("created_at", "2022-03-08")->get();

        self::assertCount(4, $collection);
        $collection->each(function ($item){
            Log::info(json_encode($item));
        });
    }

    public function testUpdate()
    {
        $this->insertCategories();

        DB::table("categories")->where("id", "=", "SMARTPHONE")->update([
            "name" => "HANDPHONE"
        ]);

        $collection = DB::table("categories")->where("name", "=", "HANDPHONE")->get();
        self::assertCount(1, $collection);
        $collection->each(function ($item){
            Log::info(json_encode($item));
        });
    }

    public function testUpsert()
    {
        DB::table("categories")->updateOrInsert([
            "id" => "VOUCHER"
        ], [
            "name" => "Voucher",
            "description" => "Ticket and Voucher",
            "created_at" => "2022-03-08 20:20:20"
            ]);

        $collection = DB::table("categories")->where("id", "=", "VOUCHER")->get();
        self::assertCount(1, $collection);
        $collection->each(function ($item){
            Log::info(json_encode($item));
        });
    }

    public function testQueryBuilderIncrement()
    {
        DB::table("counters")->where('id','=', 'sample')->increment('counter',1);

        $collection = DB::table("counters")->where('id', '=', 'sample')->get();
        self::assertCount(1, $collection);
        $collection->each(function ($item){
            Log::info(json_encode($item));
        });

    }

    public function testQueryBuilderDelete()
    {
        $this->insertCategories();

        DB::table("categories")->where('id','=','SMARTPHONE')->delete();

        $collection = DB::table('categories')->where('id','=', 'SMARTPHONE')->get();
        self::assertCount(0, $collection);
    }

    public function insertProducts()
    {
        $this->insertCategories();

        DB::table("products")->insert([
            "id" => "1",
            "name" => "Iphone 15 Pro Max",
            "category_id" => "SMARTPHONE",
            "price" => 25000000
        ]);

        DB::table("products")->insert([
            "id" => "2",
            "name" => "Samsung Galaxy S23 Ultra",
            "category_id" => "SMARTPHONE",
            "price" => 20000000
        ]);
    }

    public function testQueryBuilderJoin()
    {
        $this->insertProducts();

        $collection = DB::table("products")
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.id', 'products.name', 'categories.name as category_name', 'products.price')
            ->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item){
            Log::info(json_encode($item));
        });
    }

    public function testQueryBuilderOrdering()
    {
        $this->insertProducts();

        $collection = DB::table("products")->whereNotNull("id")
            ->orderBy("price", "desc")
            ->orderBy("name","asc")->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item){
            Log::info(json_encode($item));
        });

    }

    public function testQueryBuilderPaging()
    {
        $this->insertProducts();

        $collection = DB::table("categories")
            ->skip(0)
            ->take(2)
            ->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item){
            Log::info(json_encode($item));
        });
    }

    public function insertManyCategories()
    {
        for ($i = 0; $i < 100; $i++){
            DB::table("categories")->insert([
                "id" => "CATEGORY-$i",
                "name" => "Category $i",
                "created_at" => "2024-03-03 10:10:10"
            ]);
        }
    }

    public function testQueryBuilderChunkResult()
    {
        $this->insertManyCategories();

        DB::table("categories")
            ->orderBy('id')
            ->chunk(10, function ($categories){
                self::assertNotNull($categories);
                Log::info("Start Chunk");
                $categories->each(function ($category){
                    Log::info(json_encode($category));
                });
                Log::info("End Chunk");
            });
    }

    public function testQueryBuilderLazyResult()
    {
        $this->insertManyCategories();

        $collection = DB::table("categories")
            ->orderBy('id')
            ->lazy(10)->take(3);

        self::assertNotNull($collection);

        $collection->each(function ($item){
            Log::info(json_encode($item));
        });
    }

    public function testQueryBuilderCursorResult()
    {
        $this->insertManyCategories();

        $collection = DB::table("categories")
            ->orderBy('id')
            ->cursor();

        self::assertNotNull($collection);

        $collection->each(function ($item){
            Log::info(json_encode($item));
        });
    }

    public function testQueryBuilderAggregate()
    {
        $this->insertProducts();

        $result = DB::table("products")->count("id");
        self::assertEquals(2, $result);

        $result = DB::table("products")->min("price");
        self::assertEquals(20000000, $result);

        $result = DB::table("products")->max("price");
        self::assertEquals(25000000, $result);

        $result = DB::table("products")->avg("price");
        self::assertEquals(22500000, $result);

        $result = DB::table("products")->sum("price");
        self::assertEquals(45000000, $result);
    }

    public function testQueryBuilderRawAggregate()
    {
        $this->insertProducts();

        $collection = DB::table("products")
            ->select(
                DB::raw('count(id) as total_product'),
                DB::raw('min(price) as min_price'),
                DB::raw('max(price) as max_price'),
            )->get();

        self::assertEquals(2, $collection[0]->total_product);
        self::assertEquals(20000000, $collection[0]->min_price);
        self::assertEquals(25000000, $collection[0]->max_price);
    }

    public function insertProductFood()
    {
        DB::table("products")->insert([
            "id" => "3",
            "name" => "Bakso",
            "category_id" => "FOOD",
            "price" => 20000
        ]);

        DB::table("products")->insert([
            "id" => "4",
            "name" => "Mie Aceh",
            "category_id" => "FOOD",
            "price" => 20000
        ]);
    }

    public function testQueryBuilderGroupBy()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $collection = DB::table("products")
            ->select("category_id", DB::raw("count(*) as total_product"))
            ->groupBy("category_id")
            ->orderBy("category_id", "desc")
            ->get();

        self::assertCount(2, $collection);
        self::assertEquals("SMARTPHONE", $collection[0]->category_id);
        self::assertEquals("FOOD", $collection[1]->category_id);
        self::assertEquals(2, $collection[0]->total_product);
        self::assertEquals(2, $collection[1]->total_product);
    }

    public function testQueryBuilderHaving()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $collection = DB::table("products")
            ->select("category_id", DB::raw("count(*) as total_product"))
            ->groupBy("category_id")
            ->orderBy("category_id")
            ->having(DB::raw("count(*)"),">", 2)
            ->get();

        assertCount(0, $collection);
    }

    public function testQueryBuilderLocking()
    {
        $this->insertProducts();

        DB::transaction(function (){
           $collection = DB::table("products")
               ->where("id", "1")
               ->lockForUpdate()
               ->get();

           self::assertCount(1, $collection);
        });
    }

    public function testQueryBuilderPagination()
    {
        $this->insertCategories();

        $paginate = DB::table("categories")
            ->paginate(perPage: 2, page: 2);

        self::assertEquals(2, $paginate->currentPage());
        self::assertEquals(2, $paginate->perPage());
        self::assertEquals(2, $paginate->lastPage());
        self::assertEquals(4, $paginate->total());

        $collection = $paginate->items();
        self::assertCount(2, $collection);
        foreach ($collection as $item){
            Log::info(json_encode($item));
        }
    }

    public function testQueryBuilderIterateAllPagination()
    {
        $this->insertCategories();

        $page = 1;

        while (true){
            $paginate = DB::table("categories")
                ->paginate(perPage: 2, page: $page);

            if ($paginate->isEmpty()){
                break;
            }else{
                $page++;

                $collection = $paginate->items();
                self::assertCount(2, $collection);
                foreach ($collection as $item){
                    Log::info(json_encode($item));
                }
            }
        }
    }

    public function testQueryBuilderCursorPagination()
    {
        $this->insertCategories();

        $cursor = "id";
        while(true){
            $paginate = DB::table("categories")->orderBy("id")->cursorPaginate(perPage: 2, cursor: $cursor);

            foreach ($paginate->items() as $item){
                self::assertNotNull($item);
                Log::info(json_encode($item));
            }

            $cursor = $paginate->nextCursor();
            if ($cursor == null){
                break;
            }
        }
    }


}
