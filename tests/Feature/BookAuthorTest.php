<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookAuthorTest extends TestCase
{
    use RefreshDatabase;

    public function testBookBelongsToAuthor()
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $this->assertEquals($author->id, $book->author_id);
        $this->assertEquals($author->id, $book->author->id);
    }

    public function testBookCanBeCreatedWithAuthor()
    {
        $author = Author::factory()->create();

        $bookData = [
            'title' => 'Adventures of Tom Sawyer',
            'author_id' => $author->id,
        ];

        $response = $this->withoutMiddleware()->post('/books', $bookData);

        $this->assertDatabaseHas('books', $bookData);
        $response->assertRedirect('/books');
    }

    public function testBookCannotBeCreatedWithoutAuthor()
    {
        $bookData = [
            'title' => 'Adventures of Tom Sawyer',
        ];

        $response = $this->post('/books', $bookData);

        $response->assertSessionHasErrors(['author_id']);
        $this->assertEquals(0, Book::count());
    }

    public function testBookCanBeUpdatedWithDifferentAuthor()
    {
        $author1 = Author::factory()->create();
        $author2 = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author1->id]);

        $updatedData = [
            'title' => $book->title,
            'author_id' => $author2->id,
        ];

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)->put("/books/{$book->id}", $updatedData);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'author_id' => $author2->id]);
        $response->assertRedirect('/books');
    }

    public function testBookCreateFormShowsAuthorList()
    {
        $authors = Author::factory(3)->create();

        $response = $this->get('/books/create');

        $response->assertStatus(200);
        foreach ($authors as $author) {
            $response->assertSee($author->first_name);
        }
    }

    public function testBookEditFormShowsAuthorList()
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $authors = Author::factory(2)->create();

        $response = $this->get("/books/{$book->id}/edit");

        $response->assertStatus(200);
        foreach (Author::all() as $a) {
            $response->assertSee($a->first_name);
        }
    }

    public function testBooksIndexDisplaysAuthorName()
    {
        $author = Author::factory()->create([
            'first_name' => 'Mark',
            'last_name' => 'Twain',
        ]);
        $book = Book::factory()->create(['author_id' => $author->id]);

        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertSee('Mark');
        $response->assertSee('Twain');
    }

    public function testBookHasForeignKeyToAuthor()
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'author_id' => $author->id,
        ]);
    }

    public function testAuthorHasManyBooks()
    {
        $author = Author::factory()->create();
        $books = Book::factory(3)->create(['author_id' => $author->id]);

        $this->assertCount(3, $author->books);
    }
}
