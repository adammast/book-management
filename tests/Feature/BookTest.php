<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Book;

class BookTest extends TestCase
{
    use RefreshDatabase; // Ensures a fresh database for each test

    /**
     * Test that all books are displayed on the books index page.
     *
     * This test ensures that the books index page loads correctly and displays
     * the titles of all books in the database.
     *
     * @return void
     */
    public function testBooksAreDisplayed()
    {
        $books = Book::factory(3)->create();

        $response = $this->get('/books');

        $response->assertStatus(200)
            ->assertViewIs('books.index');

        foreach ($books as $book) {
            $response->assertSee($book->title);
        }
    }

    /**
     * Test that a new book can be created.
     *
     * This test ensures that when valid book data is provided, a new book is
     * added to the database, and the user is redirected to the books index page.
     *
     * @return void
     */
    public function testBookCanBeCreated()
    {
        $bookData = [
            'title' => 'Adventures of Tom Sawyer',
            'author' => 'Mark Twain',
        ];

        $response = $this->withoutMiddleware()->post('/books', $bookData);

        $this->assertDatabaseHas('books', $bookData);

        $response->assertRedirect('/books');
    }

    /**
     * Test that creating a book fails with missing fields.
     *
     * This test ensures that when book data is incomplete (missing title or author),
     * the creation request fails and the appropriate validation errors are shown.
     *
     * @return void
     */
    public function testBookCreationFailsWithMissingFields()
    {
        $response = $this->withoutMiddleware()->post('/books', []);

        $response->assertSessionHasErrors(['title', 'author']);

        $this->assertEquals(0, Book::count());
    }

    /**
     * Test that a book can be deleted.
     *
     * This test ensures that when a delete request is made for a book, the book is
     * removed from the database and the user is redirected to the books index page.
     *
     * @return void
     */
    public function testBookCanBeDeleted()
    {
        $book = Book::factory()->create();

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)->delete("/books/{$book->id}");

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $response->assertRedirect('/books');
    }

    /**
     * Test that a book can be updated.
     *
     * This test ensures that when a put request is made to update a book's data,
     * the changes are reflected in the database, and the user is redirected to the
     * books index page.
     *
     * @return void
     */
    public function testBookCanBeUpdated()
    {
        $book = Book::factory()->create();

        $updatedData = [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
        ];

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)->put("/books/{$book->id}", $updatedData);

        $this->assertDatabaseHas('books', $updatedData);
        $response->assertRedirect('/books');
    }

    /**
     * Test that CSV export returns a valid CSV file with the correct columns.
     *
     * This test ensures that when the exportCsv method is called, the CSV file is 
     * generated correctly, and the correct data is included.
     *
     * @return void
     */
    public function testExportCsv()
    {
        $books = Book::factory(3)->create();

        $response = $this->get('/export/csv');

        $response->assertStatus(200);

        // Assert that the response is a CSV file
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="books.csv"');

        // Get the content of the CSV export
        $content = $response->streamedContent();

        // Check if the CSV contains the correct header and data
        $this->assertStringContainsString('title,author', $content);
        foreach ($books as $book) {
            $this->assertStringContainsString($book->title, $content);
            $this->assertStringContainsString($book->author, $content);
        }
    }

    /**
     * Test that exporting only the title column works correctly in the CSV export.
     *
     * This test ensures that when the request specifies the 'title' column, 
     * only the title is included in the CSV export.
     *
     * @return void
     */
    public function testExportCsvWithTitleColumnOnly()
    {
        $books = Book::factory(3)->create();

        // Make the request to export the CSV with only the 'title' column
        $response = $this->get('/export/csv?columns[]=title');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="books.csv"');

        $content = $response->streamedContent();

        // Check that the content includes only the 'title' column header
        $this->assertStringContainsString('title', $content);
        $this->assertStringNotContainsString('author', $content);

        foreach ($books as $book) {
            // Check that the content includes the book titles
            $this->assertStringContainsString($book->title, $content);
        }
    }

    /**
     * Test that exporting only the author column works correctly in the CSV export.
     *
     * This test ensures that when the request specifies the 'author' column, 
     * only the author is included in the CSV export.
     *
     * @return void
     */
    public function testExportCsvWithAuthorColumnOnly()
    {
        $books = Book::factory(3)->create();

        // Make the request to export the CSV with only the 'author' column
        $response = $this->get('/export/csv?columns[]=author');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="books.csv"');

        $content = $response->streamedContent();

        // Check that the content includes only the 'author' column header
        $this->assertStringContainsString('author', $content);
        $this->assertStringNotContainsString('title', $content);

        foreach ($books as $book) {
            // Check that the content includes the book authors
            $this->assertStringContainsString($book->author, $content);
        }
    }

    /**
     * Test that XML export returns a valid XML file with the correct structure.
     *
     * This test ensures that the exportXml method generates a valid XML response 
     * with the correct book data.
     *
     * @return void
     */
    public function testExportXml()
    {
        $books = Book::factory(3)->create();

        $response = $this->get('/export/xml');

        $response->assertStatus(200);

        // Assert that the response is an XML file
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertHeader('Content-Disposition', 'attachment; filename="books.xml"');

        // Check if the XML contains the correct book data
        $xmlContent = $response->getContent();
        foreach ($books as $book) {
            $this->assertStringContainsString("<title>{$book->title}</title>", $xmlContent);
            $this->assertStringContainsString("<author>{$book->author}</author>", $xmlContent);
        }
    }

    /**
     * Test that exporting only the title column works correctly in the XML export.
     *
     * This test ensures that when the request specifies the 'title' column, 
     * only the title is included in the XML export.
     *
     * @return void
     */
    public function testExportXmlWithTitleColumnOnly()
    {
        $books = Book::factory(3)->create();

        // Make the request to export the XML with only the 'title' column
        $response = $this->get('/export/xml?columns[]=title');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertHeader('Content-Disposition', 'attachment; filename="books.xml"');

        $content = $response->getContent();

        // Check that the XML includes only the 'title' tag for each book
        foreach ($books as $book) {
            $this->assertStringContainsString("<title>{$book->title}</title>", $content);
            $this->assertStringNotContainsString("<author>{$book->author}</author>", $content);
        }
    }

    /**
     * Test that exporting only the author column works correctly in the XML export.
     *
     * This test ensures that when the request specifies the 'author' column, 
     * only the author is included in the XML export.
     *
     * @return void
     */
    public function testExportXmlWithAuthorColumnOnly()
    {
        $books = Book::factory(3)->create();

        // Make the request to export the XML with only the 'author' column
        $response = $this->get('/export/xml?columns[]=author');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertHeader('Content-Disposition', 'attachment; filename="books.xml"');

        $content = $response->getContent();

        // Check that the XML includes only the 'author' tag for each book
        foreach ($books as $book) {
            $this->assertStringContainsString("<author>{$book->author}</author>", $content);
            $this->assertStringNotContainsString("<title>{$book->title}</title>", $content);
        }
    }
}