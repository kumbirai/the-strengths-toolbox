<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BlogCommentController extends Controller
{
    /**
     * Store a new comment
     */
    public function store(Request $request, string $slug)
    {
        $post = BlogPost::where('slug', $slug)->where('is_published', true)->first();

        if (! $post) {
            return redirect()->route('blog.show', $slug)
                ->with('error', 'Blog post not found.');
        }

        $validator = Validator::make($request->all(), [
            'author_name' => 'required|string|max:255',
            'author_email' => 'required|email|max:255',
            'author_website' => 'nullable|url|max:255',
            'content' => 'required|string|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            return redirect()->route('blog.show', $slug)
                ->withErrors($validator)
                ->withInput()
                ->with('comment_error', 'Please correct the errors below.');
        }

        // Basic spam detection
        $isSpam = $this->detectSpam($request->all());

        $comment = BlogComment::create([
            'blog_post_id' => $post->id,
            'parent_id' => null,
            'author_name' => $request->author_name,
            'author_email' => $request->author_email,
            'author_website' => $request->author_website,
            'content' => $request->content,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_approved' => ! $isSpam, // Auto-approve if not spam
            'is_spam' => $isSpam,
        ]);

        if ($isSpam) {
            return redirect()->route('blog.show', $slug)
                ->with('info', 'Your comment has been submitted and will be reviewed before being published.');
        }

        return redirect()->route('blog.show', $slug)
            ->with('success', 'Thank you for your comment!')
            ->withFragment('comments');
    }

    /**
     * Store a reply to a comment
     */
    public function storeReply(Request $request, string $slug, BlogComment $comment)
    {
        $post = BlogPost::where('slug', $slug)->where('is_published', true)->first();

        if (! $post) {
            return redirect()->route('blog.show', $slug)
                ->with('error', 'Blog post not found.');
        }

        // Verify comment belongs to post
        if ($comment->blog_post_id !== $post->id) {
            return redirect()->route('blog.show', $slug)
                ->with('error', 'Invalid comment.');
        }

        $validator = Validator::make($request->all(), [
            'author_name' => 'required|string|max:255',
            'author_email' => 'required|email|max:255',
            'author_website' => 'nullable|url|max:255',
            'content' => 'required|string|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            return redirect()->route('blog.show', $slug)
                ->withErrors($validator)
                ->withInput()
                ->with('comment_error', 'Please correct the errors below.')
                ->withFragment('comment-'.$comment->id);
        }

        // Basic spam detection
        $isSpam = $this->detectSpam($request->all());

        $reply = BlogComment::create([
            'blog_post_id' => $post->id,
            'parent_id' => $comment->id,
            'author_name' => $request->author_name,
            'author_email' => $request->author_email,
            'author_website' => $request->author_website,
            'content' => $request->content,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_approved' => ! $isSpam,
            'is_spam' => $isSpam,
        ]);

        if ($isSpam) {
            return redirect()->route('blog.show', $slug)
                ->with('info', 'Your reply has been submitted and will be reviewed before being published.')
                ->withFragment('comment-'.$comment->id);
        }

        return redirect()->route('blog.show', $slug)
            ->with('success', 'Thank you for your reply!')
            ->withFragment('comment-'.$comment->id);
    }

    /**
     * Basic spam detection
     */
    protected function detectSpam(array $data): bool
    {
        $content = strtolower($data['content'] ?? '');
        $name = strtolower($data['author_name'] ?? '');
        $email = strtolower($data['author_email'] ?? '');

        // Common spam patterns
        $spamPatterns = [
            '/\b(buy|cheap|discount|free|viagra|cialis|casino|poker|loan|debt)\b/i',
            '/http[s]?:\/\/[^\s]+/i', // Multiple URLs
        ];

        $urlCount = preg_match_all('/http[s]?:\/\/[^\s]+/i', $content);
        if ($urlCount > 2) {
            return true;
        }

        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $content) || preg_match($pattern, $name)) {
                return true;
            }
        }

        return false;
    }
}
