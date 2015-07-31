<?php
namespace Wheniwork\Feedback\Service;

use \HieuLe\WordpressXmlrpcClient\WordpressClient;

class BlogService
{
    private $client;
    private $users;

    /**
     * Constructs a new service to get blog comments for feedback.
     * @param WordpressClient $client The Wordpress client to use to get comments.
     * @param array           $users  An array of user ids that can later be used to filter comments.
     */
    public function __construct(WordpressClient $client, array $users = array())
    {
        $this->client = $client;
        $this->users = $users;
    }

    /**
     * Get a specific Wordpress comment by its ID.
     *
     * @param int|string $comment_id The id of the comment to retrieve.
     *
     * @link https://codex.wordpress.org/XML-RPC_WordPress_API/Comments#wp.getComment
     */
    public function getComment($comment_id)
    {
        return $this->client->getComment($comment_id);
    }

    /**
     * Get published comments (excluding pingbacks) from Wordpress. Comments
     * are sorted in chronological order, with most recent comments first.
     *
     * @param int $after_time The timestamp after which to retrieve comments.
     * @param bool $filter_users Whether to filter comments based on the provided array of users.
     * @param int $num_comments The number of comments to retrieve.
     *
     * @link https://codex.wordpress.org/XML-RPC_WordPress_API/Comments#wp.getComments
     */
    public function getPublishedComments($after_time = 0, $filter_users = true, $num_comments = 50)
    {
        $comments = $this->client->getComments([
            'status' => 'approve',
            'number' => $num_comments
        ]);
        $comments = array_filter($comments, function ($comment) use ($after_time, $filter_users) {
            $not_pingback = $comment['type'] != 'pingback';
            $is_after = $comment['date_created_gmt']->timestamp > $after_time;
            $from_known_user = true;
            if ($filter_users && !in_array($comment['user_id'], $this->users)) {
                $from_known_user = false;
            }
            return $not_pingback && $is_after && $from_known_user;
        });

        $comments = array_values($comments);
        return $comments;
    }
}
