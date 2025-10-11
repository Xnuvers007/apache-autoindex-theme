<?php
ob_start();

header('X-Frame-Options: SAMEORIGIN');

function isAllowedFile($file, $allowedExtensions)
{
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    return in_array(strtolower($extension), $allowedExtensions);
}

$allowedExtensions = [
    'html', 'css', 'js', 'env', 'php', 'txt', 'json', 'xml', 'env', 'gitignore', 'md',
    'yml', 'yaml', 'ini', 'conf', 'log', 'htaccess', 'htpasswd', 'csv', 'tsv', 'sql',
    'c', 'cpp', 'h', 'java', 'py', 'rb', 'sh', 'bat', 'pl', 'go', 'rs', 'swift', 'ts',
    'phtml', 'shtml', 'xhtml', 'jsp', 'asp', 'aspx', 'jspx', 'cfm', 'cfml',
    'scss', 'less', 'sass', 'vue', 'jsx', 'tsx', 'dart', 'lua', 'r', 'm', 'erl', 'hs',
    'groovy', 'kt', 'kts', 'sql', 'ps1', 'psm1', 'vbs', 'vb', 'asm', 'makefile', 'dockerfile'
];

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $file = $_GET['file'] ?? '';

    if ($action === 'read' && is_file($file)) {
        if (!isAllowedFile($file, $allowedExtensions)) {
            echo "This file type is not allowed to be edited.";
            exit;
        }
        echo file_get_contents($file);
        exit;
    }

    if ($action === 'save' && is_file($file)) {
        if (!isAllowedFile($file, $allowedExtensions)) {
            echo "This file type is not allowed to be edited.";
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        file_put_contents($file, $data['content']);
        echo "File saved successfully!";
        exit;
    }

    if ($action === 'rename' && is_file($file)) {
        $newName = $_GET['newName'] ?? '';
        $newPath = dirname($file) . '/' . $newName;
        if (rename($file, $newPath)) {
            echo "File renamed successfully!";
        } else {
            echo "Failed to rename file.";
        }
        exit;
    }

    if ($action === 'listFiles') {
        $fileList = [];
        foreach ($files as $file) {
            $filePath = $currentDir . '/' . $file;
            $fileList[] = [
                'name' => $file,
                'date' => date("F d Y H:i:s.", filemtime($filePath)),
                'type' => is_dir($filePath) ? 'Folder' : 'File',
                'size' => is_dir($filePath) ? humanFileSize(getFolderSize($filePath)) : humanFileSize(filesize($filePath))
            ];
        }
        echo json_encode($fileList);
        exit;
    }    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="description" content="file manager of htdocs or /var/www/html">
    <meta name="language" content="id">
    <meta name="author" content="Lukman754 & Xnuvers007">
    <meta name="keywords" content="htdocs,html,filemanager">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">

    <meta itemprop="name" content="File Manager htdocs (/var/www/html)">
    <meta itemprop="description" content="file manager of htdocs or /var/www/html">
    <meta itemprop="image" content=" ">

    <meta property="og:url" content="http://localhost:80">
    <meta property="og:type" content="website" />
    <meta property="og:title" content="File Manager htdocs (/var/www/html)" />
    <meta property="og:description" content="file manager of htdocs or /var/www/html" />
    <meta property="og:image" content=" " />
    <meta property="og:site_name" content="File Manager htdocs (/var/www/html)" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="File Manager htdocs (/var/www/html)" />
    <meta name="twitter:description" content="file manager of htdocs or /var/www/html" />
    <meta name="twitter:image" content=" " />

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <title>File Manager Htdocs</title>
    <style>
        body {
            background-color: #1e1e1e;
            color: #d4d4d4;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            transition: background-color 0.3s, color 0.3s;
        }


        .footer {
            display: block;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            padding: 15px 20px;
            font-size: 10px;
        }

        .footer p {
            margin-bottom: 15px;
        }

        .footer a {
            color: #fcd53f;
            text-decoration: none;
            background-color: #2d2d2d;
            border-radius: 5px;
            padding: 5px 10px;
        }


        .footer a:hover {
            color: #ffb02e;
            /* Warna teks link saat di-hover */
        }

        .footer strong {
            font-weight: normal;
            /* Set weight ke normal untuk konsistensi */
        }


        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
        }

        .search-container {
            margin-bottom: 10px;
        }

        .search-container input[type="text"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 0;
            border-radius: 4px;
            background-color: #252526;
            color: #d4d4d4;
        }


        .search-container button .sort-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .file-table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
            font-size: 12px;
        }

        .file-table th,
        .file-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #2d2d2d;
        }

        .file-table th {
            background-color: #252526;
            font-weight: normal;
            cursor: pointer;
            position: relative;
        }

        .file-table th .sort-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .file-table tr:hover {
            background-color: #2a2d2e;
        }

        .folder-icon::before,
        .file-icon::before {
            margin-right: 5px;
        }

        .folder-icon::before {
            content: "📁";
            color: white;
        }

        .file-icon::before {
            content: "📄";
            color: white;
        }

        .file-table a {
            text-decoration: none;
            color: inherit;
        }

        .file-table th:nth-child(1),
        .file-table td:nth-child(1) {
            width: 40%;
        }

        .file-table th:nth-child(2),
        .file-table td:nth-child(2) {
            width: 25%;
        }

        .file-table th:nth-child(3),
        .file-table td:nth-child(3) {
            width: 20%;
        }

        .file-table th:nth-child(4),
        .file-table td:nth-child(4) {
            width: 15%;
        }

        .grey-text {
            color: #a0a0a0;
            font-size: 12px;
        }

        .highlight {
            background-color: #fcd53f;
            color: black;
        }

        .light-mode {
            background-color: #f0f0f0;
            color: #333;
        }

        .light-mode .container {
            background-color: #fff;
            color: #333;
        }

        .light-mode .search-container input[type="text"] {
            background-color: #e0e0e0;
            color: #333;
        }

        .light-mode .search-container button {
            background-color: #e0e0e0;
            color: #333;
        }

        .light-mode .file-table th,
        .light-mode .file-table td {
            border-bottom: 1px solid #ddd;
        }

        .light-mode .file-table tr:hover {
            background-color: #f5f5f5;
        }

        .light-mode .folder-icon::before,
        .light-mode .file-icon::before {
            color: black;
        }

        .light-mode .file-table th {
            background-color: #fff;
            color: #333;
        }

        .light-mode .file-table th .sort-icon {
            color: #333;
        }

        .sort-icon {
            color: inherit;
        }

        .sort-icon {
            color: #d4d4d4;
        }

        @media (max-width: 768px) {

            .search-container input[type="text"],
            .search-container button {
                flex: 1 1 100%;
            }
        }

        body.light-mode {
            background-color: #ffffff;
            /* Warna latar untuk tema terang */
            color: #333333;
            /* Warna teks untuk tema terang */
        }

        body.dark-mode {
            background-color: #1c1c1c;
            /* Warna latar untuk tema gelap */
            color: #ffffff;
            /* Warna teks untuk tema gelap */
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: inherit;
            /* Ikuti warna latar body */
        }

        .button {
            display: flex;
            gap: 10px;
        }

        .toogle {
            padding: 8px 16px;
            font-size: 14px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        body.light-mode .toogle {
            background-color: #2d2d2d;
        }

        body.dark-mode .toogle {
            background-color: #444;
        }


        .light-mode .toogle {
            padding: 8px 16px;
            font-size: 14px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .toogle:hover {
            background-color: #2d2d2d;
            color: #d4d4d4;
        }

        .loader {
            border: 16px solid #f3f3f3;
            border-top: 16px solid #3498db;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
            margin: 0 auto;
            display: none;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        #loading {
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        #loading h1 {
            margin: 10px 0;
        }

        .dark-mode .path-color {
            color: yellow;
        }

        .light-mode .path-color {
            color: red;
        }

        .pagination a {
    display: inline-block;
    padding: 8px 12px;
    margin: 0 5px;
    text-decoration: none;
    color: #007bff;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: background-color 0.3s, color 0.3s;
}

.pagination a:hover {
    background-color: #007bff;
    color: #fff;
}

.pagination a[style*="color: #fcd53f"] {
    background-color: #fcd53f;
    color: #000;
    font-weight: bold;
}
    </style>
    <script>
        function goBack() {
            const currentDir = window.location.search ? new URLSearchParams(window.location.search).get('dir') : './';
            const parentDir = currentDir.substring(0, currentDir.lastIndexOf('/')) || './';
            window.location.href = '?dir=' + encodeURIComponent(parentDir);
        }


        function searchFiles() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("fileTable");
            tr = table.getElementsByTagName("tr");

            if (!filter) {
                // If input is empty, redirect to the root or main folder
                window.location.href = '?dir=./'; // Redirect to root folder
                return;
            }

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0]; // Targeting only the name cell
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        // Highlight only the matched text
                        const link = td.getElementsByTagName("a")[0];
                        if (link) {
                            const highlightedText = txtValue.replace(new RegExp(filter, "gi"), match => `<span class='highlight'>${match}</span>`);
                            link.innerHTML = highlightedText;
                        }
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function parseSize(sizeStr) {
            let size = parseFloat(sizeStr);
            if (sizeStr.includes('GB')) return size * (1 << 30);
            if (sizeStr.includes('MB')) return size * (1 << 20);
            if (sizeStr.includes('KB')) return size * (1 << 10);
            return size;
        }

        function sortTable(columnIndex, sortOrder) {
            var table, rows, switching, i, x, y, shouldSwitch;
            table = document.getElementById("fileTable");
            switching = true;

            while (switching) {
                switching = false;
                rows = table.rows;

                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[columnIndex];
                    y = rows[i + 1].getElementsByTagName("TD")[columnIndex];

                    let xValue = columnIndex === 3 ? parseSize(x.textContent) : x.textContent.toLowerCase();
                    let yValue = columnIndex === 3 ? parseSize(y.textContent) : y.textContent.toLowerCase();

                    if (sortOrder) {
                        if (xValue > yValue) {
                            shouldSwitch = true;
                            break;
                        }
                    } else {
                        if (xValue < yValue) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                }
            }
        }

        function sortByName() {
            sortTable(0, nameSortOrder);
            nameSortOrder = !nameSortOrder;
            updateSortIcons();
        }

        function sortByDate() {
            sortTable(1, dateSortOrder);
            dateSortOrder = !dateSortOrder;
            updateSortIcons();
        }

        function sortBySize() {
            sortTable(3, sizeSortOrder);
            sizeSortOrder = !sizeSortOrder;
            updateSortIcons();
        }

        function sortByType() {
            sortTable(2, typeSortOrder);
            typeSortOrder = !typeSortOrder;
            updateSortIcons();
        }

        function updateSortIcons() {
            var nameSortIcon = document.getElementById("nameSortIcon");
            var dateSortIcon = document.getElementById("dateSortIcon");
            var sizeSortIcon = document.getElementById("sizeSortIcon");
            var typeSortIcon = document.getElementById("typeSortIcon");

            if (nameSortIcon) {
                nameSortIcon.textContent = nameSortOrder ? "▴" : "▾";
            }
            if (dateSortIcon) {
                dateSortIcon.textContent = dateSortOrder ? "▴" : "▾";
            }
            if (sizeSortIcon) {
                sizeSortIcon.textContent = sizeSortOrder ? "▴" : "▾";
            }
            if (typeSortIcon) {
                typeSortIcon.textContent = typeSortOrder ? "▴" : "▾";
            }
        }

        let nameSortOrder = true;
        let dateSortOrder = true;
        let sizeSortOrder = true;
        let typeSortOrder = true;

        updateSortIcons();

        function updateTime() {
            var now = new Date();
            var day = now.getDate();
            var month = now.getMonth() + 1;
            var year = now.getFullYear();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();

            // var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            var formattedDateTime = days[now.getDay()] + " " + day + " " + getMonthName(month) + " " + year + " \n " + hours + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" + seconds : seconds);
            var datetimeElement = document.getElementById("datetime");
            if (datetimeElement) {
                datetimeElement.textContent = formattedDateTime;
            }
        }

        function getMonthName(month) {
            // var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            return months[month - 1];
        }

        updateTime();
        setInterval(updateTime, 1000);


        window.onload = function () {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') {
                document.body.classList.add('light-mode');
            } else {
                document.body.classList.add('dark-mode');
            }
            updatePathColor();
        };

        function toggleMode() {
            const body = document.body;
            const toggleButton = document.querySelector(".toogle");

            // Toggle kelas untuk light dan dark mode
            body.classList.toggle('light-mode');
            body.classList.toggle('dark-mode');

            // Tentukan mode saat ini dan simpan di localStorage
            const currentMode = body.classList.contains('light-mode') ? 'light' : 'dark';
            localStorage.setItem('theme', currentMode);

            // Perbarui teks tombol berdasarkan mode
            toggleButton.textContent = currentMode === 'light' ? 'Dark 🌙' : 'Light ☀️';

            updatePathColor();
        }

        // Inisialisasi tema saat halaman dimuat
        document.addEventListener("DOMContentLoaded", () => {
            const savedTheme = localStorage.getItem('theme');
            const body = document.body;
            const toggleButton = document.querySelector(".toogle");

            // Atur tema berdasarkan preferensi yang tersimpan
            if (savedTheme === 'light') {
                body.classList.add('light-mode');
                body.classList.remove('dark-mode');
                toggleButton.textContent = 'Dark 🌙';
            } else {
                body.classList.add('dark-mode');
                body.classList.remove('light-mode');
                toggleButton.textContent = 'Light ☀️';
            }
        });

        let currentFilePath = '';

function openEditor(filePath) {
    const allowedExtensions = [
    'html', 'css', 'js', 'env', 'php', 'txt', 'json', 'xml', 'env', 'gitignore', 'md',
    'yml', 'yaml', 'ini', 'conf', 'log', 'htaccess', 'htpasswd', 'csv', 'tsv', 'sql',
    'c', 'cpp', 'h', 'java', 'py', 'rb', 'sh', 'bat', 'pl', 'go', 'rs', 'swift', 'ts',
    'phtml', 'shtml', 'xhtml', 'jsp', 'asp', 'aspx', 'jspx', 'cfm', 'cfml',
    'scss', 'less', 'sass', 'vue', 'jsx', 'tsx', 'dart', 'lua', 'r', 'm', 'erl', 'hs',
    'groovy', 'kt', 'kts', 'sql', 'ps1', 'psm1', 'vbs', 'vb', 'asm', 'makefile', 'dockerfile'
    ];

    const fileExtension = filePath.split('.').pop().toLowerCase();

    if (!allowedExtensions.includes(fileExtension)) {
        alert('This file type is not allowed to be edited.');
        return;
    }

    currentFilePath = filePath;

    const editorModal = document.getElementById('textEditorModal');

    fetch(`?action=read&file=${encodeURIComponent(filePath)}`)
        .then(response => response.text())
        .then(content => {
            document.getElementById('editorContent').value = content;
            document.getElementById('textEditorModal').style.display = 'block';

            editorModal.scrollIntoView({ behavior: 'smooth', block: 'start' });
        })
        .catch(error => alert('Failed to open file: ' + error));
}

function saveFile() {
    const content = document.getElementById('editorContent').value;

    fetch(`?action=save&file=${encodeURIComponent(currentFilePath)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ content })
    })
        .then(response => response.text())
        .then(result => alert(result))
        .catch(error => alert('Failed to save file: ' + error));
}

function renameFile() {
    const newName = prompt('Enter new file name:');
    if (!newName) return;

    fetch(`?action=rename&file=${encodeURIComponent(currentFilePath)}&newName=${encodeURIComponent(newName)}`)
        .then(response => response.text())
        .then(result => {
            alert(result);
            closeEditor();
            location.reload();
        })
        .catch(error => alert('Failed to rename file: ' + error));
}

function replaceText() {
    const searchText = prompt('Enter text to search:');
    const replaceText = prompt('Enter replacement text:');
    if (!searchText || !replaceText) return;

    const editor = document.getElementById('editorContent');
    editor.value = editor.value.split(searchText).join(replaceText);
}

function viewFile() {
    if (!currentFilePath) {
        alert('No file selected to view.');
        return;
    }
    window.open(currentFilePath, '_blank');
}

function closeEditor() {
    document.getElementById('textEditorModal').style.display = 'none';
}

document.addEventListener("DOMContentLoaded", function () {
    const fileTableBody = document.querySelector("#fileTable tbody");
    const loadingIndicator = document.getElementById("loading");

    fetch(`?action=listFiles&dir=${encodeURIComponent(currentDir)}`)
        .then(response => response.json())
        .then(files => {
            loadingIndicator.style.display = "none";
            files.forEach(file => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${file.name}</td>
                    <td>${file.date}</td>
                    <td>${file.type}</td>
                    <td>${file.size}</td>
                `;
                fileTableBody.appendChild(row);
            });
        })
        .catch(error => console.error("Failed to load files:", error));
});

        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("loading").style.display = "none";
            document.querySelector(".container").style.display = "block";

            const rows = document.querySelectorAll("#fileTable tbody tr");
            rows.forEach(row => {
                const link = row.querySelector("a");
                if (link) {
                    row.style.cursor = "pointer";
                    row.addEventListener("click", () => {
                        window.location.href = link.href;
                    });
                }
                const editButton = row.querySelector("button[onclick^='openEditor']");
                if (editButton) {
                    editButton.addEventListener("click", (event) => {
                        event.stopPropagation();
                    });
                }
            });
        });
        
    </script>
</head>

<body>
    <div id="loading">
        <div class="loader"></div>
        <h1>Loading...</h1>
    </div>
    <div class="container">
        <h1>File Explorer</h1>
        <header>
            <div class="info">
                <p id="datetime"></p>
            </div>
            <div class="button">
                <button onclick="toggleMode()" class="toogle" type="button">Toggle Light/Dark Mode</button>
                <button onclick="goBack()" class="toogle" type="button">Back</button>
            </div>
        </header>

        <div class="search-container">
            <input type="text" id="searchInput" onkeyup="searchFiles()" placeholder="Search for files...">
        </div>
        <table class="file-table" id="fileTable">
            <thead>
                <tr>
                    <th onclick="sortByName()">Name <span id="nameSortIcon" class="sort-icon">▴</span></th>
                    <th onclick="sortByDate()">Date modified <span id="dateSortIcon" class="sort-icon">▴</span></th>
                    <th onclick="sortByType()">Type <span id="typeSortIcon" class="sort-icon">▴</span></th>
                    <th onclick="sortBySize()">Size <span id="sizeSortIcon" class="sort-icon">▴</span></th>
                </tr>
            </thead>
            <tbody>
                <?php
                function humanFileSize($size, $unit = "")
                {
                    if ((!$unit && $size >= 1 << 30) || $unit == "GB")
                        return number_format($size / (1 << 30), 2) . " GB";
                    if ((!$unit && $size >= 1 << 20) || $unit == "MB")
                        return number_format($size / (1 << 20), 2) . " MB";
                    if ((!$unit && $size >= 1 << 10) || $unit == "KB")
                        return number_format($size / (1 << 10), 2) . " KB";
                    return number_format($size) . " bytes";
                }

                function getFolderSize($dir)
                {
                    static $cache = [];
                    if (isset($cache[$dir])) {
                        return $cache[$dir];
                    }
                
                    $totalSize = 0;
                    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
                        if ($file->isFile()) {
                            $totalSize += $file->getSize();
                        }
                    }
                
                    $cache[$dir] = $totalSize;
                    return $totalSize;
                }

                $currentDir = isset($_GET['dir']) ? $_GET['dir'] : './';
                $files = array_diff(scandir($currentDir), array('.', '..'));

                $filesPerPage = 10;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $totalFiles = count($files);
                $totalPages = ceil($totalFiles / $filesPerPage);
                $startIndex = ($page - 1) * $filesPerPage;
                $files = array_slice($files, $startIndex, $filesPerPage);
                
                foreach ($files as $file) {
                    $filePath = $currentDir . '/' . $file;
                    $fileSize = is_dir($filePath) ? humanFileSize(getFolderSize($filePath)) : humanFileSize(filesize($filePath));
                    $fileDate = date("F d Y H:i:s.", filemtime($filePath));
                    $fileType = filetype($filePath);
                
                    echo "<tr>";
                    if (is_dir($filePath)) {
                        echo "<td class='folder-icon'><a href='?dir=" . urlencode($filePath) . "'>$file</a></td>";
                        echo "<td>$fileDate</td>";
                        echo "<td>Folder</td>";
                        echo "<td class='grey-text'>$fileSize</td>";
                    } else {
                        echo "<td class='file-icon'><a href='" . htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') . "' target='_blank'>" . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . "</a></td>";                        echo "<td>$fileDate</td>";
                        echo "<td>$fileType</td>";
                        echo "<td>$fileSize</td>";
                
                        if (isAllowedFile($filePath, $allowedExtensions)) {
                            echo "<td><button onclick=\"openEditor('" . htmlspecialchars($filePath) . "')\" style='background-color: #007bff; color: #fff; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer;'>Edit</button></td>";
                        } else {
                            echo "<td></td>";
                        }
                    }
                    echo "</tr>";
                }
                ?>
                
                <div class="pagination" style="text-align: center; margin-top: 20px;">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?dir=<?php echo urlencode($currentDir); ?>&page=<?php echo $i; ?>" 
                           style="margin: 0 5px; text-decoration: none; color: <?php echo $i === $page ? '#fcd53f' : '#007bff'; ?>;">
                           <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </tbody>
        </table>
    </div>
    
    <div id="textEditorModal" style="display: none;">
    <div style="background-color: #252526; padding: 20px; border-radius: 8px; width: 80%; margin: 50px auto; color: #d4d4d4;">
        <h2>Text Editor</h2>
        <textarea id="editorContent" style="width: 100%; height: 300px; background-color: #1e1e1e; color: #d4d4d4; border: 1px solid #444; padding: 10px; border-radius: 4px; font-family: 'Courier New', Courier, monospace; resize: none; box-sizing: border-box;"></textarea>
        <div style="margin-top: 10px; display: flex; justify-content: space-between;">
            <button onclick="saveFile()" style="background-color: #007bff; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Save</button>
            <button onclick="renameFile()" style="background-color: #fcd53f; color: #000; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Rename</button>
            <button onclick="replaceText()" style="background-color: #ff5722; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Replace</button>
            <button onclick="viewFile()" style="background-color: #28a745; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">View</button>
            <button onclick="closeEditor()" style="background-color: #444; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Exit</button>
        </div>
    </div>
</div>

    <footer class="footer">
        <p>&copy; <?php echo htmlspecialchars(date('Y'), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars(gethostname(), ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.</p>
        <a href="https://github.com/lukman754/apache-autoindex-theme" target="_blank" rel="noopener noreferrer">
            Created by <span class="github-icon"><i class="fab fa-github"></i></span> Lukman754 & <span
                class="github-icon"><i class="fab fa-github"></i></span> Xnuvers007
        </a>
    </footer>
</body>
</html>

<?php
ob_end_flush();
?>
