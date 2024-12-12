<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Colaborativa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fdfd96; /* Amarelo claro */
            font-family: Arial, sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .note-container {
            width: 90%;
            max-width: 600px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        textarea {
            width: 100%;
            height: 200px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            resize: none;
            font-size: 16px;
            background-color: #fcfcfc;
        }
        .btn-container {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }
        .btn {
            width: 48%;
        }
    </style>
</head>
<body>
    <div class="note-container">
        <h2 class="text-center mb-3">Nota Colaborativa</h2>
        <textarea id="note" placeholder="Digite sua nota..."></textarea>
        <div class="btn-container">
            <button id="edit" class="btn btn-outline-secondary">Editar</button>
            <button id="save" class="btn btn-primary">Salvar</button>
        </div>
    </div>

    <script>
        const note = document.getElementById('note');
        const saveButton = document.getElementById('save');
        const editButton = document.getElementById('edit');

        const ws = new WebSocket('ws://localhost:8080'); // Atualize para o endereço do servidor WebSocket

        // Bloquear edição inicialmente
        note.disabled = true;

        // Receber atualizações em tempo real
        ws.onmessage = function(event) {
            note.value = event.data;
        };

        // Salvar alterações
        saveButton.addEventListener('click', () => {
            ws.send(note.value);
            note.disabled = true;
        });

        // Permitir edição
        editButton.addEventListener('click', () => {
            note.disabled = false;
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
