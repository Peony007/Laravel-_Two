<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{
    public function addItem(Request $request){
       $title = $request->title;
       $itemDataArray = array(
        'title'=>$title
       );
       $newItem = Item::create($itemDataArray);
       if(!is_null($newItem)) {
           return response()->json(["success"=>true, "data"=>$newItem]);
       }
        
    }
    public function itemList(){
        $item = Item::all();
        return response()->json(["success"=>true, "data"=>$item]);
    }

    public function removeItem($id){
        $delItem = Item::find($id);
  
        $del = Item::where("id", $id)->delete();
        return response()->json(["success"=>true, "data"=>$delItem]);

    }
}
