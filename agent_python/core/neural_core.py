import numpy as np
from core.tokenizer import SOLPITokenizer
from core.embeddings import SOLPIEmbeddings
from core.positional_encoding import SOLPIPositionalEncoding
from core.attention import SOLPISelfAttention
from core.feed_forward import SOLPIFeedForward
from core.config import SOLPIConfig

class SOLPINeuralCore:
    """
    CÉREBRO v27.0 - Evoluindo para um Transformer Real.
    Integrando Pacotes 04, 05, 06, 07, 08 e 10.
    """
    def __init__(self):
        self.config = SOLPIConfig()
        self.tokenizer = SOLPITokenizer()
        
        # Módulos Reais
        self.embeddings = SOLPIEmbeddings(self.config.VOCAB_SIZE, self.config.EMBED_DIM)
        self.pos_encoding = SOLPIPositionalEncoding(self.config.MAX_SEQ_LEN, self.config.EMBED_DIM)
        self.attention = SOLPISelfAttention(self.config.EMBED_DIM)
        self.feed_forward = SOLPIFeedForward(self.config.EMBED_DIM, self.config.EMBED_DIM * 4)

    def think_native(self, text):
        # 1. Tokenização (Pacote 04)
        tokens = self.tokenizer.encode(text)
        if not tokens: return "Entrada vazia."

        # 2. Embeddings Treináveis (Pacote 06)
        x = self.embeddings.forward(tokens)
        
        # 3. Positional Encoding (Pacote 07)
        x = self.pos_encoding.forward(x)
        
        # 4. Self-Attention QKV (Pacote 08)
        attn_out = self.attention.forward(x)
        x = x + attn_out # Residual simples
        
        # 5. Feed Forward (Pacote 10)
        ff_out = self.feed_forward.forward(x)
        x = x + ff_out # Residual simples
        
        return f"🧠 [NATIVE-REAL-CORE v27]: Processamento espacial e sequencial concluído ({len(tokens)} tokens)."
