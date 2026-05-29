<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthorsAreDisplayed()
    {
        $authors = Author::factory(3)->create();

        $response = $this->get('/authors');

        $response->assertStatus(200);

        foreach ($authors as $author) {
            $response->assertSee($author->first_name);
            $response->assertSee($author->last_name);
        }
    }

    public function testAuthorsIndexHasCreateButton()
    {
        $response = $this->get('/authors');

        $response->assertStatus(200);
        $response->assertSee(route('authors.create'));
    }

    public function testAuthorCanBeCreated()
    {
        $authorData = [
            'first_name' => 'Mark',
            'last_name' => 'Twain',
        ];

        $response = $this->withoutMiddleware()->post('/authors', $authorData);

        $this->assertDatabaseHas('authors', $authorData);
        $response->assertRedirect('/authors');
    }

    public function testAuthorCreationFailsWithMissingFields()
    {
        $response = $this->post('/authors', []);

        $response->assertSessionHasErrors(['first_name', 'last_name']);

        $this->assertEquals(0, Author::count());
    }

    public function testAuthorCanBeUpdated()
    {
        $author = Author::factory()->create();

        $updatedData = [
            'first_name' => 'Updated First',
            'last_name' => 'Updated Last',
        ];

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)->put("/authors/{$author->id}", $updatedData);

        $this->assertDatabaseHas('authors', $updatedData);
        $response->assertRedirect('/authors');
    }

    public function testAuthorCanBeDeletedWhenNotTiedToBooks()
    {
        $author = Author::factory()->create();

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)->delete("/authors/{$author->id}");

        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
        $response->assertRedirect('/authors');
    }

    public function testAuthorCannotBeDeletedWhenTiedToBooks()
    {
        $author = Author::factory()->create();
        Book::factory()->create(['author_id' => $author->id]);

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)->delete("/authors/{$author->id}");

        $this->assertDatabaseHas('authors', ['id' => $author->id]);
    }

    public function testAuthorHasFirstNameAndLastName()
    {
        $author = Author::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Austen',
        ]);

        $this->assertEquals('Jane', $author->first_name);
        $this->assertEquals('Austen', $author->last_name);
    }

    public function testAuthorFactoryCreatesValidAuthor()
    {
        $author = Author::factory()->create();

        $this->assertNotNull($author->first_name);
        $this->assertNotNull($author->last_name);
        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'first_name' => $author->first_name,
            'last_name' => $author->last_name,
        ]);
    }
}
