<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'socket.room.enter'                 => [\App\Listeners\Room\Enter::class],
        'socket.room.leave'                 => [\App\Listeners\Room\Leave::class],
        'socket.room.user.changeName'       => [\App\Listeners\Room\UserChangeName::class],
        'socket.room.user.kick'             => [\App\Listeners\Room\UserKick::class],
        'socket.room.vote.start'            => [\App\Listeners\Room\VoteStart::class],
        'socket.room.vote'                  => [\App\Listeners\Room\Vote::class],
        'socket.room.vote.reset'            => [\App\Listeners\Room\VoteReset::class],
        'socket.room.vote.revote'           => [\App\Listeners\Room\Revote::class],
        'socket.room.card.shake'            => [\App\Listeners\Room\CardShake::class],
        'socket.room.user.toggle.hasVote'   => [\App\Listeners\Room\UserToggleHasVote::class],

        'socket.room.task.update.all'       => [\App\Listeners\Room\TaskUpdateAll::class],
        'socket.room.task.approve'          => [\App\Listeners\Room\TaskApprove::class],

        'socket.room.chat.send'             => [\App\Listeners\Room\ChatSend::class],

        'socket.user.connect'               => [\App\Listeners\UserConnect::class],

        'server.socket.start'               => [\App\Listeners\Start::class],
        'server.socket.stop'                => [\App\Listeners\Stop::class],
        'server.socket.webSocketConnect'    => [\App\Listeners\WebSocketConnect::class],
        'server.socket.message'             => [\App\Listeners\Message::class],
        'server.socket.close'               => [\App\Listeners\Close::class],

        'server.socket.sendToAll'           => [\App\Listeners\Socket\SendToAll::class],

        'server.room.vote.finish'           => [\App\Listeners\Room\VoteFinish::class],
        'server.online.update'              => [\App\Listeners\OnlineUpdate::class],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
