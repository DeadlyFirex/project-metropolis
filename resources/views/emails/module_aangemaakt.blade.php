<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Module aangemaakt</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px;">
    <table style="max-width: 600px; margin: auto; background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <tr>
            <td style="text-align: center;">
                <h2 style="font-size: 24px; color: #1f2937; margin-bottom: 20px;">Nieuwe module aangemaakt</h2>
            </td>
        </tr>
        <tr>
            <td style="text-align: center;">
                <img src="{{ asset('storage/' . $module->image_path) }}"
                     alt="{{ $module->name }}"
                     style="width: 80px; height: 80px; object-fit: contain; border-radius: 6px; margin-bottom: 20px;">
            </td>
        </tr>
        <tr>
            <td style="padding: 10px 0;">
                <p><strong>Naam:</strong> {{ $module->name }}</p>
                <p><strong>Beschrijving:</strong> {{ $module->description }}</p>
                <p><strong>Categorie:</strong> {{ $module->category }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
