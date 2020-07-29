<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Vedmant\FeedReader\Facades\FeedReader;
use App\Feed;
use Twilio\Rest\Client;
class SendSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $feed = FeedReader::read(env('RSS_FEED'));
        foreach ($feed->get_items() as $item)
        {
            $feed = Feed::where('title', $item->get_title())->first();
            if (!$feed)
            {
                Feed::create([
                    'title' => $item->get_title(),
                    'description' => $item->get_description(),
                    'date' => $item->get_date('Y-m-d'),
                ]);

                $account_sid = env("TWILIO_SID");
                $auth_token = env("TWILIO_AUTH_TOKEN");
                $twilio_number = env("TWILIO_NUMBER");
                $client = new Client($account_sid, $auth_token);
                $client->messages->create(env("TWILIO_TO_NUMBER"), ['from' => $twilio_number, 'body' => $item->get_title() . "-" . substr($item->get_description(), 0, 300)]);

            }
        }
    }
}
