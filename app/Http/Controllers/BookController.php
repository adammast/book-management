<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends Controller
{
    /**
     * Display a listing of all books.
     *
     * This method retrieves all books from the database and passes them to the view for display.
     *
     * @return \Illuminate\View\View The view that displays the list of books.
     */
    public function index()
    {
        $books = Book::with('author')->get();
        return view('books.index', compact('books'));
    }

    /**
     * Show the form to create a new book.
     *
     * This method returns the view for the form where users can input the details for a new book.
     *
     * @return \Illuminate\View\View The view that displays the create book form.
     */
    public function create()
    {
        $authors = \App\Models\Author::all();
        return view('books.create', compact('authors'));
    }

    /**
     * Store a new book in the database.
     *
     * This method validates the request data, creates a new book in the database using the validated data,
     * and then redirects the user back to the index page.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the new book data.
     * @return \Illuminate\Http\RedirectResponse A redirect response to the index page.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author_id' => 'required|exists:authors,id',
        ]);

        Book::create($request->all());

        return redirect()->route('books.index');
    }

    /**
     * Delete a specific book.
     *
     * This method deletes a book from the database and redirects the user back to the index page.
     *
     * @param \App\Models\Book $book The book to delete.
     * @return \Illuminate\Http\RedirectResponse A redirect response to the index page.
     */
    public function destroy(Book $book)
    {
        $book->delete();
        return redirect()->route('books.index');
    }

    /**
     * Show the form to edit an existing book.
     *
     * This method returns the view for the form to edit the details of a specific book.
     *
     * @param \App\Models\Book $book The book whose details will be edited.
     * @return \Illuminate\View\View The view that displays the edit book form.
     */
    public function edit(Book $book)
    {
        $authors = \App\Models\Author::all();
        return view('books.edit', compact('book', 'authors'));
    }

    /**
     * Update an existing book in the database.
     *
     * This method validates the request data, updates the book in the database with the new data,
     * and then redirects the user back to the index page.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the updated book data.
     * @param \App\Models\Book $book The book to update.
     * @return \Illuminate\Http\RedirectResponse A redirect response to the index page.
     */
    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author_id' => 'required|exists:authors,id',
        ]);

        $book->update($request->all());

        return redirect()->route('books.index');
    }


    /**
     * Export book data as a CSV file.
     *
     * This method retrieves a list of books from the database and streams the data to the browser 
     * in CSV format. The columns to be exported are passed via the request, with default columns 
     * being 'title' and 'author'.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse The streamed response that triggers the CSV download.
     */
    public function exportCsv(Request $request)
    {
        $columns = $request->input('columns', ['title']);
        $includeAuthor = in_array('author', $columns);

        $books = Book::with('author')->get();

        $response = new StreamedResponse(function () use ($books, $columns, $includeAuthor) {
            $handle = fopen('php://output', 'w');

            $headers = [];
            foreach ($columns as $column) {
                if ($column === 'title') {
                    $headers[] = 'title';
                } elseif ($column === 'author' && $includeAuthor) {
                    $headers[] = 'author_first_name';
                    $headers[] = 'author_last_name';
                }
            }
            fputcsv($handle, $headers);

            foreach ($books as $book) {
                $row = [];
                foreach ($columns as $column) {
                    if ($column === 'title') {
                        $row[] = $book->title;
                    } elseif ($column === 'author' && $includeAuthor && $book->author) {
                        $row[] = $book->author->first_name;
                        $row[] = $book->author->last_name;
                    }
                }
                fputcsv($handle, $row);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="books.csv"');

        return $response;
    }

    /**
     * Export book data as an XML file.
     *
     * This method retrieves a list of books from the database and generates an XML representation 
     * of the data. The columns to be exported are passed via the request, with default columns 
     * being 'title' and 'author'.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request.
     * @return \Illuminate\Http\Response The XML response containing the book data, triggering the download of the XML file.
     */
    public function exportXml(Request $request)
    {
        $columns = $request->input('columns', ['title']);
        $includeAuthor = in_array('author', $columns);

        $books = Book::with('author')->get();

        if ($includeAuthor) {
            $xml = new \SimpleXMLElement('<authors/>');
            
            $booksByAuthor = $books->groupBy('author_id');
            
            foreach ($booksByAuthor as $authorId => $authorBooks) {
                $author = $authorBooks->first()->author;
                if ($author) {
                    $authorElement = $xml->addChild('author');
                    $authorElement->addChild('first_name', htmlspecialchars($author->first_name));
                    $authorElement->addChild('last_name', htmlspecialchars($author->last_name));
                    
                    foreach ($authorBooks as $book) {
                        $bookElement = $authorElement->addChild('book');
                        $bookElement->addChild('title', htmlspecialchars($book->title));
                    }
                }
            }
        } else {
            $xml = new \SimpleXMLElement('<books/>');
            
            foreach ($books as $book) {
                $bookElement = $xml->addChild('book');
                $bookElement->addChild('title', htmlspecialchars($book->title));
            }
        }

        return response($xml->asXML(), 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="books.xml"');
    }
}