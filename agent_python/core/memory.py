import sqlite3
import os

class LongTermMemory:
    def __init__(self, db_path="memory/agent_memory.db"):
        self.db_path = db_path
        self._init_db()

    def _init_db(self):
        os.makedirs(os.path.dirname(self.db_path), exist_ok=True)
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()

        # Tabela FTS5 para busca rápida de texto (Cérebro do Hermes)
        cursor.execute('''
            CREATE VIRTUAL TABLE IF NOT EXISTS memories
            USING fts5(content, context, timestamp UNINDEXED)
        ''')

        conn.commit()
        conn.close()

    def store(self, content, context="general"):
        import datetime
        timestamp = datetime.datetime.now().isoformat()
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        cursor.execute(
            "INSERT INTO memories (content, context, timestamp) VALUES (?, ?, ?)",
            (content, context, timestamp)
        )
        conn.commit()
        conn.close()

    def search(self, query, limit=5):
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        # Busca por relevância usando o motor FTS5
        cursor.execute(
            "SELECT content, context, timestamp FROM memories WHERE memories MATCH ? ORDER BY rank LIMIT ?",
            (query, limit)
        )
        results = cursor.fetchall()
        conn.close()
        return results
