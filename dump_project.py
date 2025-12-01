import os

# Konfigurasi
PROJECT_PATH = "."  # Folder saat ini
OUTPUT_FILE = "full_code_dump.txt"

# Folder yang akan diabaikan (supaya file tidak meledak ukurannya)
IGNORED_DIRS = {
    'vendor', 'node_modules', '.git', '.idea', '.vscode', 
    'storage', 'public/build', 'public/vendor'
}

# Ekstensi file yang akan dibaca
ALLOWED_EXTENSIONS = {
    '.php', '.js', '.css', '.json', '.blade.php', '.yml', '.yaml', '.xml', '.env.example'
}

# File spesifik yang sangat penting untuk dicek
PRIORITY_FILES = [
    'vite.config.js',
    'composer.json',
    'package.json',
    '.env.example',
    'config/app.php',
    'config/livewire.php',
    'config/filament.php'
]

def is_ignored(path):
    parts = path.split(os.sep)
    for part in parts:
        if part in IGNORED_DIRS:
            return True
    return False

def get_file_content(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            return f.read()
    except Exception as e:
        return f"[Error reading file: {e}]"

def main():
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as out:
        out.write(f"--- PROJECT DUMP ---\n\n")
        
        for root, dirs, files in os.walk(PROJECT_PATH):
            # Filter folder yang diabaikan
            dirs[:] = [d for d in dirs if d not in IGNORED_DIRS]
            
            if is_ignored(root):
                continue

            for file in files:
                ext = os.path.splitext(file)[1]
                filepath = os.path.join(root, file)
                
                # Cek apakah file masuk kriteria
                if ext in ALLOWED_EXTENSIONS or file in PRIORITY_FILES:
                    print(f"Reading: {filepath}")
                    content = get_file_content(filepath)
                    
                    out.write(f"\n{'='*50}\n")
                    out.write(f"FILE: {filepath}\n")
                    out.write(f"{'='*50}\n")
                    out.write(content + "\n")

    print(f"\nSelesai! File output tersimpan di: {OUTPUT_FILE}")
    print("Silakan upload file 'full_code_dump.txt' tersebut ke chat ini.")

if __name__ == "__main__":
    main()