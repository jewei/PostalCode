# Postal codes

Find or validate postal codes.

## Usage

    use Jewei\PostalCode\PostalCode;

    $postcode = new PostalCode();
    $found = $postcode->find('Belleville ON K8N 5W6', 'Canada'); // K8N 5W6
    $validate = $postcode->validate('K8N 5W6', 'Canada'); // true
