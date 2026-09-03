<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    /**
     * Public endpoint — called from the website contact form.
     */
    public function store(Request $request)
    {
        $validated=$request->validate([
            'name'=>'required|string|max:150',
            'email'=>'required|email|max:150',
            'subject'=>'required|string|max:200',
            'message'=>'required|string|max:5000',
        ]);

        $validated['ip_address']=$request->ip();
        $validated['user_agent']=substr(
            (string)$request->userAgent(),
            0,
            255
        );

        $contactMessage=ContactMessage::create($validated);

        return response()->json([
            'success'=>true,
            'message'=>'আপনার বার্তা সফলভাবে পাঠানো হয়েছে।',
            'data'=>$contactMessage
        ],201);
    }

    /**
     * Admin — list contact messages.
     */
    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:read,unread',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $query=ContactMessage::query()
            ->with('reader:id,name,email')
            ->latest('id');

        if(($validated['status']??null)==='read'){
            $query->where('is_read',true);
        }elseif(($validated['status']??null)==='unread'){
            $query->where('is_read',false);
        }

        if(!empty($validated['search'])){
            $search=$validated['search'];

            $query->where(function($q)use($search){
                $q->where('name','like',"%{$search}%")
                    ->orWhere('email','like',"%{$search}%")
                    ->orWhere('subject','like',"%{$search}%")
                    ->orWhere('message','like',"%{$search}%");
            });
        }

        return response()->json([
            'success'=>true,
            'unread_count'=>ContactMessage::unread()->count(),
            'data'=>$query->paginate(
                min((int)($validated['per_page']??15),100)
            )
        ]);
    }

    /**
     * Admin — view a single contact message (marks it as read).
     */
    public function show(Request $request,ContactMessage $contactMessage)
    {
        if(!$contactMessage->is_read){
            $contactMessage->update([
                'is_read'=>true,
                'read_at'=>now(),
                'read_by'=>$request->user()->id,
            ]);
        }

        return response()->json([
            'success'=>true,
            'data'=>$contactMessage->load('reader:id,name,email')
        ]);
    }

    /**
     * Admin — toggle read / unread status manually.
     */
    public function toggleRead(Request $request,ContactMessage $contactMessage)
    {
        $contactMessage->update(
            $contactMessage->is_read
                ?[
                    'is_read'=>false,
                    'read_at'=>null,
                    'read_by'=>null,
                ]
                :[
                    'is_read'=>true,
                    'read_at'=>now(),
                    'read_by'=>$request->user()->id,
                ]
        );

        return response()->json([
            'success'=>true,
            'message'=>$contactMessage->is_read
                ?'Message marked as read.'
                :'Message marked as unread.',
            'data'=>$contactMessage
        ]);
    }

    /**
     * Admin — delete a contact message.
     */
    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return response()->json([
            'success'=>true,
            'message'=>'Contact message deleted successfully.'
        ]);
    }
}