<?php namespace App\Http\Controllers;

		use Session;
		use Request;
		use DB;
		use CRUDBooster;

		use Illuminate\Support\Facades\Http;


		class ApiEnviamensajeController extends \crocodicstudio\crudbooster\controllers\ApiController {

		    function __construct() {    
				$this->table       = "outbox";        
				$this->permalink   = "enviamensaje";    
				$this->method_type = "post";    
		    }
		

		    public function hook_before(&$postdata) 
		    {
		        //This method will be execute before run the main process
				$number = explode("|", $postdata['number']);
				$device = DB::table('device')->select('name')->where('id',$postdata['id_device'])->first();
				if($postdata['type'] == "Text")
				{
					$body = ['text'=>$postdata['text']];
				}

				foreach ($number as $q) 
				{
					$format_number = $this->formatNumber($q);
					$data[] = [
						'jid' => $format_number.'@s.whatsapp.net',
						'type' => 'number',
						'delay' => 5000,
						'message' => $body
					];
				}

				if(count($number) ==1)
				{
					// single number
					$format_number = $this->formatNumber($number[0]);
					$response = Http::withHeaders([
						'Content-Type' => 'application/json'
					  ])->post(env('URL_WA_SERVER').'/'.$device->name.'/messages/send', 
						[
						'jid' => $format_number.'@s.whatsapp.net',
						'type' => 'number',
						'message' => $body
						]
					);
				}
				else
				{
					// bulk number
					$response = Http::withHeaders([
						'Content-Type' => 'application/json'					
						])->post(env('URL_WA_SERVER').'/'.$device->name.'/messages/send/bulk', $data
							);					 			
				}
				$res = json_decode($response->getBody());
				$status = $res->error ? $res->error : $res->status;
				$postdata['status'] = $res->status;
		    }

			public function formatNumber($number)
			{
				if ($number[0] == "0" || $number[0] == "8") 
				{
					$format_number = env('REGIONAL').substr($number, 1);
				}
				else
				{
					$format_number = $number;
				}
				return $format_number;
			}


		    public function hook_query(&$query) 
		    {
		        //This method is to customize the sql query

		    }

		    public function hook_after($postdata,&$result) 
		    {
		        //This method will be execute after run the main process

		    }

		}