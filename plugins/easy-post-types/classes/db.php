<?php
class CustomFields_db {

    public function deleteContentField($contentType, $fieldName) {
        global $wpdb;

        $contentType = stripslashes($contentType);
        $fieldName = stripslashes($fieldName);

        $querystr = "DELETE
            FROM $wpdb->postmeta wpostmeta
            WHERE wpostmeta.post_id IN (
                SELECT ID FROM $wpdb->posts wposts
                WHERE wposts.post_type = '$contentType' )
            AND wpostmeta.meta_key = '$fieldName'";

        $wpdb->query($querystr);
    }

    public function deleteTaxonomy($contentType) {
        global $wpdb;

        $contentType = stripslashes($contentType);

        $querystr = "DELETE
            FROM $wpdb->term_relationships 
            WHERE object_id IN (
                SELECT ID FROM $wpdb->posts wposts
                WHERE wposts.post_type = '$contentType' )";

        $wpdb->query($querystr);
    }

    public function deleteCommentsMeta($contentType) {
        global $wpdb;

        $contentType = stripslashes($contentType);

        $querystr = "DELETE
            FROM $wpdb->commentmeta wcommentmeta
            where wcommentmeta.comment_id IN (
                SELECT wcomment.commetn_ID
                FROM $wpdb->comments wcomment 
                WHERE wcomment.comment_post_ID in (
                    SELECT ID FROM $wpdb->posts wposts
                    WHERE wposts.post_type = '$contentType' )
                )";

        $wpdb->query($querystr);
    }

    public function deleteComments($contentType) {
        global $wpdb;

        $contentType = stripslashes($contentType);

        $this->deleteCommentsMeta($contentType);

        $querystr = "DELETE
            FROM $wpdb->comments 
            WHERE comment_post_ID IN (
                SELECT ID FROM $wpdb->posts wposts
                WHERE wposts.post_type = '$contentType' )";

        $wpdb->query($querystr);
    }

    public function deleteAllContentField($contentType) {
        global $wpdb;

        $contentType = stripslashes($contentType);
        $fieldName = stripslashes($fieldName);

        $querystr = "DELETE
            FROM $wpdb->postmeta 
            WHERE post_id IN (
                SELECT ID FROM $wpdb->posts wposts
                WHERE wposts.post_type = '$contentType' )";

        $wpdb->query($querystr);
    }

    public function deleteContentType($contentType) {
        global $wpdb;

        $contentType = stripslashes($contentType);
        $fieldName = stripslashes($fieldName);

        $this->deleteAllContentField($contentType);
        $this->deleteTaxonomy($contentType);
        $this->deleteComments($contentType);

        $querystr = "DELETE
                FROM $wpdb->posts 
                WHERE post_type = '$contentType'";

        $wpdb->query($querystr);
    }

    public function moveContentType($contentType) {
        global $wpdb;

        $contentType = stripslashes($contentType);
        $fieldName = stripslashes($fieldName);

        $querystr = "UPDATE $wpdb->posts wpost
                SET wpost.post_type='post'
                WHERE post_type = '$contentType'";

        $wpdb->query($querystr);
    }

    public function getPostsPerContentField($contentType, $fieldName) {
        global $wpdb;
        
        $contentType = stripslashes($contentType);
        $fieldName = stripslashes($fieldName);

        $querystr = "SELECT count(wposts.ID) as number
            FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
            WHERE wposts.ID = wpostmeta.post_id 
            AND wposts.post_type = '$contentType'
            AND wpostmeta.meta_key = '$fieldName'";
        $row = $wpdb->get_row($querystr, OBJECT, 0);
        if ($row) {
            return $row->number;
        }
        else {
            return 0;
        }
    }
    
    public function getPostsPerContent($contentType) {
        global $wpdb;

        $contentType = stripslashes($contentType);
        $querystr = "SELECT count(wposts.ID) as number
            FROM $wpdb->posts wposts
            WHERE wposts.post_type = '$contentType'";

        $row = $wpdb->get_row($querystr, OBJECT, 0);
        if ($row) {
            return $row->number;
        }
        else {
            return 0;
        }
    }

    public function getStatsPerContent($contentType) {
        global $wpdb;
        $results=array();
        $contentType = stripslashes($contentType);
        $querystr = "SELECT COUNT(wposts.id) AS n, wposts.post_type as type, wposts.post_status as status
                    FROM $wpdb->posts wposts
                    WHERE wposts.post_type = '$contentType'
                    GROUP BY wposts.post_status";
            

        $row = $wpdb->get_results($querystr);
        foreach($row as $key=>$values) {
            $results[$values->status]=$values;
        }
        return $results;
    }

    public function getStats() {
        global $wpdb;
        $results = array();
        $querystr = "SELECT COUNT(wposts.id) AS n, wposts.post_type, wposts.post_status
                    FROM $wpdb->posts wposts
                    GROUP BY wposts.post_type, wposts.post_status
                    ORDER BY wposts.post_type, wposts.post_status";

        while ($row = $wpdb->get_results($querystr)) {
            $results[]=$row;
        }

        return $results;
    }
}
?>
