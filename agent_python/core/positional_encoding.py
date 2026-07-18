import numpy as np

class SOLPIPositionalEncoding:
    """
    PACOTE 07: POSITIONAL ENCODING v1.0
    Adiciona informação de posição aos embeddings. 
    Usa funções seno e cosseno para representar distâncias relativas.
    """
    def __init__(self, max_len, embed_dim):
        self.encoding = np.zeros((max_len, embed_dim))
        position = np.arange(0, max_len)[:, np.newaxis]
        div_term = np.exp(np.arange(0, embed_dim, 2) * -(np.log(10000.0) / embed_dim))
        
        self.encoding[:, 0::2] = np.sin(position * div_term)
        self.encoding[:, 1::2] = np.cos(position * div_term)

    def forward(self, x):
        """Adiciona o encoding à sequência de entrada."""
        seq_len = x.shape[0]
        return x + self.encoding[:seq_len, :]
