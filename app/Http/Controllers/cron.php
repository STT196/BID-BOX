<?php



namespace App\Http\Controllers;



use App\Models\Bid;

use App\Models\Product;

use App\Models\winner;

use Carbon\Carbon;

use Illuminate\Http\Request;



use function PHPSTORM_META\type;



class cron extends Controller

{

    public function expire(){

        $temps = Product::all();

        $current_time = Carbon::now();



        foreach($temps as $temp){

            $exp = Carbon::create($temp->expired_at);

            // $result=$current_time->gte($exp);

            if ($current_time->gte($exp) == 1 AND $temp->is_expired == 0 AND $temp->is_winner_selected == 0){

                 //echo $exp ,'<br>',$temp->id,'</span>','<p>',$current_time;

                // echo $temp->id,'<p>';



                $bid = Bid::where('product_id',$temp->id)->orderBy('amount','DESC')->first();

		        $temp->is_expired = 1;

                   
                $temp->update();

                if($bid){



                    winner::create([

                        'product_id' => $temp->id,

                        'customer_id' => $bid->user_id,

                        'bid_id' => $bid->id,

                    ]);

    		    $temp->is_winner_selected = 1;

                }

              

            }





        }



    }

}

