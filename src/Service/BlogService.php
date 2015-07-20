<?php
namespace Wheniwork\Feedback\Service;

use \HieuLe\WordpressXmlrpcClient\WordpressClient;

class BlogService
{
    private static function getClient()
    {
        $wp = new WordpressClient();
        $wp->setCredentials($_ENV['WP_ENDPOINT'], $_ENV['WP_USER'], $_ENV['WP_PASSWORD']);
        return $wp;
    }

    /**
     * Get a specific Wordpress comment by its ID.
     *
     * @param int|string $comment_id The id of the comment to retrieve.
     *
     * @link https://codex.wordpress.org/XML-RPC_WordPress_API/Comments#wp.getComment
     */
    public static function getComment($comment_id)
    {
        $wp = self::getClient();
        return $wp->getComment($comment_id);
    }

    /**
     * Get published comments (excluding pingbacks) from Wordpress. Comments
     * are sorted in chronological order, with most recent comments first.
     * 
     * @param int $num_comments The number of comments to retrieve.
     * @param int $after_time The timestamp after which to retrieve comments.
     *
     * @link https://codex.wordpress.org/XML-RPC_WordPress_API/Comments#wp.getComments
     */
    public static function getPublishedComments($num_comments = 50, $after_time = 0)
    {
        $wp = self::getClient();
        $comments = $wp->getComments([
            'status' => 'approve',
            'number' => $num_comments
        ]);
        $comments = array_filter($comments, function ($comment) use ($after_time) {
            $not_pingback = $comment['type'] != 'pingback';
            $is_after = $comment['date_created_gmt']->timestamp > $after_time;
            return $not_pingback && $is_after;
        });

        $comments = array_values($comments);
        return $comments;
    }
}
