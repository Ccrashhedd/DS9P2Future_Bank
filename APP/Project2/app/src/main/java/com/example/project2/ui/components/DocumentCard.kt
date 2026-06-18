package com.example.project2.ui.components

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Download
import androidx.compose.material.icons.outlined.PictureAsPdf
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.example.project2.data.DocumentoDto
import com.example.project2.ui.theme.Silver
import com.example.project2.ui.theme.SilverDark
import com.example.project2.ui.theme.TextPrimary
import com.example.project2.ui.theme.TextSecondary

@Composable
fun DocumentCard(
    documento: DocumentoDto,
    onClick: () -> Unit,
    onOpenPdf: (DocumentoDto) -> Unit,
    modifier: Modifier = Modifier
) {
    androidx.compose.material3.Surface(
        modifier = modifier.fillMaxWidth(),
        onClick = onClick,
        color = androidx.compose.ui.graphics.Color.Transparent
    ) {
        BankGlassCard(modifier = Modifier.fillMaxWidth()) {
            Row(verticalAlignment = Alignment.Top) {
                Icon(
                    imageVector = Icons.Outlined.PictureAsPdf,
                    contentDescription = null,
                    tint = Silver,
                    modifier = Modifier
                        .padding(top = 4.dp)
                        .size(34.dp)
                )
                Column(
                    modifier = Modifier
                        .weight(1f)
                        .padding(horizontal = 12.dp),
                    verticalArrangement = Arrangement.spacedBy(6.dp)
                ) {
                    Text(documento.titulo, color = TextPrimary, style = MaterialTheme.typography.titleMedium)
                    StatusChip(text = documento.tipo)
                    Text(documento.institucion, color = TextSecondary, style = MaterialTheme.typography.bodyMedium)
                    Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                        documento.fechaEmision?.let {
                            Text("Emision: $it", color = SilverDark, style = MaterialTheme.typography.labelMedium)
                        }
                        documento.totalHoras?.let {
                            Text("$it horas", color = SilverDark, style = MaterialTheme.typography.labelMedium)
                        }
                    }
                    Text(
                        text = if (documento.tienePdf) "PDF disponible" else "Sin PDF disponible",
                        color = if (documento.tienePdf) Silver else SilverDark,
                        style = MaterialTheme.typography.labelMedium
                    )
                }
                IconButton(
                    onClick = { onOpenPdf(documento) },
                    enabled = documento.tienePdf
                ) {
                    Icon(
                        Icons.Outlined.Download,
                        contentDescription = "Abrir PDF",
                        tint = if (documento.tienePdf) Silver else SilverDark
                    )
                }
            }
        }
    }
}
