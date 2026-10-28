
<?php

class Movie
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllMovies()
    {
        $sql = "SELECT * FROM movies ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchMovies($keyword)
    {
        return $this->getMoviesFiltered($keyword, '');
    }

    public function getMoviesFiltered($keyword = '', $genre = '')
    {
        $sql = "SELECT * FROM movies";
        $conditions = [];
        $params = [];

        if ($keyword !== '') {
            $conditions[] = "(
                title LIKE :title
                OR genre LIKE :genre_search
                OR director LIKE :director
            )";

            $search = '%' . $keyword . '%';

            $params['title'] = $search;
            $params['genre_search'] = $search;
            $params['director'] = $search;
        }

        if ($genre !== '') {
            $conditions[] = "genre = :selected_genre";
            $params['selected_genre'] = $genre;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY title ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMovieById($id)
    {
        $sql = "SELECT * FROM movies WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
