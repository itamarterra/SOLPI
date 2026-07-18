import re
import json
import os

class SOLPITokenizer:
    """
    PACOTE 04: TOKENIZER v1.0
    Implementa a quebra de texto em IDs numéricos (Etapa 2 e 4).
    """
    def __init__(self, vocab_file="vocabulary.json"):
        self.vocab_file = vocab_file
        self.vocab = {"<PAD>": 0, "<UNK>": 1, "<SOS>": 2, "<EOS>": 3}
        self.inverse_vocab = {}
        self.load_vocab()

    def load_vocab(self):
        if os.path.exists(self.vocab_file):
            with open(self.vocab_file, 'r', encoding='utf-8') as f:
                self.vocab = json.load(f)
        self.inverse_vocab = {v: k for k, v in self.vocab.items()}

    def encode(self, text):
        """Transforma texto em lista de IDs."""
        tokens = re.findall(r"[\w']+|[.,!?;]", text.lower())
        ids = []
        for t in tokens:
            if t not in self.vocab:
                self.vocab[t] = len(self.vocab)
            ids.append(self.vocab[t])
        self.save_vocab()
        return ids

    def decode(self, ids):
        """Transforma IDs de volta em texto."""
        return " ".join([self.inverse_vocab.get(i, "<UNK>") for i in ids])

    def save_vocab(self):
        with open(self.vocab_file, 'w', encoding='utf-8') as f:
            json.dump(self.vocab, f, indent=4)
